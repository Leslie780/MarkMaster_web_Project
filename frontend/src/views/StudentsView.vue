<template>
  <div class="student-dashboard">
    <div class="header">
      <h1>Student Dashboard</h1>
      <!-- Notifications -->
      <div class="header-actions">
        <div class="notification-bell" @click="toggleNotifications">
          <span class="bell-icon">🔔</span>
          <span v-if="unreadNotifications.length > 0" class="notification-badge">{{ unreadNotifications.length }}</span>
        </div>
        <div v-if="showNotifications" class="notification-dropdown">
          <div v-if="notifications.length > 0">
            <div v-for="notif in notifications" :key="notif.id" class="notification-item">
              <p>{{ notif.message }}</p>
              <small>{{ timeAgo(notif.created_at) }}</small>
            </div>
            <button v-if="unreadNotifications.length > 0" @click="markAllAsRead" class="mark-read-btn">Mark all as read</button>
          </div>
          <div v-else class="notification-item no-notifs">
            <p>No new notifications.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Info Section -->
    <div class="student-info" v-if="currentUser.name">
      <div class="user-profile">
        <img v-if="currentUser.profile_pic" :src="currentUser.profile_pic" :alt="currentUser.name" class="profile-pic"/>
        <div v-else class="profile-placeholder">{{ getInitials(currentUser.name) }}</div>
        <div class="user-details">
          <h2>{{ currentUser.name }}</h2>
          <p>Matric No: {{ currentUser.matric_no }}</p>
          <p>Email: {{ currentUser.email }}</p>
          <p>Status: <span :class="['status', currentUser.status]">{{ currentUser.status }}</span></p>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      <p>{{ error }}</p>
      <button @click="retryFetch" class="retry-btn">Retry</button>
    </div>

    <!-- Login Required Message -->
    <div v-if="!currentUser.id && !loading" class="login-required">
      <p>Please log in as a student to view your dashboard.</p>
      <button @click="redirectToLogin" class="login-btn">Go to Login</button>
    </div>

    <!-- Main Dashboard Content -->
    <div v-if="studentData && !loading" class="dashboard-content">
      <!-- Performance Trend Graph -->
      <div class="performance-trend-card">
        <h3>🎯 Performance Trend (CGPA per Semester)</h3>
        <div class="chart-container">
          <canvas id="cgpaTrendChart"></canvas>
        </div>
        <div v-if="chartError" class="chart-error">
          <p>{{ chartError }}</p>
          <button @click="retryChart" class="retry-btn">Retry Chart</button>
        </div>
      </div>
      
      <!-- Overall CGPA Card -->
      <div class="cgpa-card">
        <h2>Overall CGPA</h2>
        <div class="cgpa-value">{{ studentData.overall_cgpa }}</div>
        <div class="cgpa-grade">{{ getGradeFromCGPA(studentData.overall_cgpa) }}</div>
        <div class="cgpa-progress">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: (studentData.overall_cgpa / 4.0 * 100) + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Semesters Container -->
      <div class="semesters-container">
        <div v-for="semester in studentData.semesters" :key="`${semester.academic_year}-${semester.semester}`" class="semester-card">
          <div class="semester-header">
            <h3>{{ semester.academic_year }} - Semester {{ semester.semester }}</h3>
            <div class="semester-cgpa">
              <span>Semester CGPA: {{ semester.cgpa }}</span>
              <span class="grade">{{ getGradeFromCGPA(semester.cgpa) }}</span>
            </div>
          </div>

          <div class="courses-table">
            <table>
              <thead>
                <tr>
                  <th>Course Code</th>
                  <th>Course Name</th>
                  <th>Credit Hours</th>
                  <th>Total Score</th>
                  <th>Final Exam</th>
                  <th>Grade</th>
                  <th>Grade Point</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="course in semester.courses" :key="course.course_id">
                  <tr>
                    <td class="course-code">{{ course.course_code }}</td>
                    <td class="course-name">{{ course.course_name }}</td>
                    <td>{{ course.credit_hours }}</td>
                    <td class="score">{{ course.total_score }}%</td>
                    <td class="score">{{ course.final_exam_score }}%</td>
                    <td :class="['grade', getGradeClass(course.grade)]">{{ course.grade }}</td>
                    <td>{{ course.grade_point }}</td>
                    <td class="actions-cell">
                      <button @click="toggleCourseDetails(course.course_id)" class="details-btn">
                        {{ expandedCourses.includes(course.course_id) ? "Hide" : "Details" }}
                      </button>
                      <button @click="toggleCourseComparison(course.course_id)" class="compare-btn" 
                              :disabled="loadingComparison === course.course_id">
                        {{ loadingComparison === course.course_id ? "Loading..." : "Compare" }}
                      </button>
                      <button @click="openAppealDialog(course)" class="appeal-btn">Appeal</button>
                    </td>
                  </tr>

                  <!-- Course Details Row -->
                  <tr v-if="expandedCourses.includes(course.course_id)" :key="`details-${course.course_id}`" class="course-details">
                    <td colspan="8">
                      <div class="assessment-details">
                        <div class="details-header">
                          <h4>📋 Assessment Score Details</h4>
                          <button @click="toggleWhatIf(course)" :class="['what-if-btn', whatIfMode[course.course_id] ? 'active' : '']">
                            {{ whatIfMode[course.course_id] ? 'Cancel Simulation' : 'What-If Simulator' }}
                          </button>
                        </div>
                        <div class="assessments-grid">
                          <div v-for="(assessment, index) in (whatIfMode[course.course_id] ? whatIfScores[course.course_id] : course.assessment_scores)" 
                               :key="assessment.component_id" class="assessment-item">
                            <div class="assessment-name">{{ assessment.title || formatComponentType(assessment.type) }}</div>
                            <div class="assessment-score">
                              <template v-if="whatIfMode[course.course_id]">
                                <input type="number" 
                                       v-model.number="whatIfScores[course.course_id][index].mark_obtained" 
                                       class="what-if-input" 
                                       :max="assessment.max_mark" 
                                       min="0"
                                       @input="updateWhatIfTotal(course.course_id)"/> / {{ assessment.max_mark }}
                              </template>
                              <template v-else>{{ assessment.mark_obtained }} / {{ assessment.max_mark }}</template>
                              ({{ assessment.percentage }}%)
                            </div>
                            <div v-if="assessment.remarks && !whatIfMode[course.course_id]" class="assessment-remarks">
                              <strong>📝 Lecturer's Remark:</strong>
                              <p>{{ assessment.remarks }}</p>
                            </div>
                          </div>
                        </div>
                        <!-- What-if Simulator Result -->
                        <div v-if="whatIfMode[course.course_id]" class="what-if-result">
                          <h4>🎯 Simulated Result</h4>
                          <p>New Total Score: <strong>{{ whatIfTotals[course.course_id]?.toFixed(2) || '0.00' }}%</strong></p>
                          <p>New Grade: <strong class="grade-text">{{ getGradeFromScore(whatIfTotals[course.course_id] || 0) }}</strong></p>
                          <p>Grade Change: <span :class="getGradeChangeClass(course.grade, getGradeFromScore(whatIfTotals[course.course_id] || 0))">
                            {{ getGradeChange(course.grade, getGradeFromScore(whatIfTotals[course.course_id] || 0)) }}
                          </span></p>
                        </div>
                      </div>
                    </td>
                  </tr>

                  <!-- Course Comparison Row -->
                  <tr v-if="expandedComparisons.includes(course.course_id)" :key="`comparison-${course.course_id}`" class="course-comparison">
                    <td colspan="8">
                      <div class="comparison-details">
                        <div class="comparison-header">
                          <h4>📊 Course Performance Comparison</h4>
                          <div v-if="courseComparisons[course.course_id]" class="ranking-info">
                            <span class="ranking-badge">
                              Rank: {{ courseComparisons[course.course_id].ranking }} / {{ courseComparisons[course.course_id].total_students }}
                            </span>
                            <span class="percentile">
                              Top {{ getPercentile(courseComparisons[course.course_id].ranking, courseComparisons[course.course_id].total_students) }}%
                            </span>
                          </div>
                        </div>
                        <div v-if="courseComparisons[course.course_id]" class="comparison-grid">
                          <div v-for="comparison in courseComparisons[course.course_id].comparisons" 
                               :key="comparison.component_id" class="comparison-item">
                            <div class="component-name">{{ comparison.title || formatComponentType(comparison.type) }}</div>
                            <div class="scores-comparison">
                              <div class="score-item my-score">
                                <label>My Score:</label>
                                <span class="score-value">{{ comparison.my_score }} / {{ comparison.max_mark }}</span>
                                <span class="score-percentage">({{ (comparison.my_score / comparison.max_mark * 100).toFixed(1) }}%)</span>
                              </div>
                              <div class="score-item class-avg">
                                <label>Class Average:</label>
                                <span class="score-value">{{ comparison.avg_score }} / {{ comparison.max_mark }}</span>
                                <span class="score-percentage">({{ (comparison.avg_score / comparison.max_mark * 100).toFixed(1) }}%)</span>
                              </div>
                              <div class="score-item difference">
                                <label>Difference:</label>
                                <span :class="['difference-value', getDifferenceClass(comparison.my_score - comparison.avg_score)]">
                                  {{ comparison.my_score > comparison.avg_score ? "+" : "" }}{{ (comparison.my_score - comparison.avg_score).toFixed(1) }}
                                </span>
                              </div>
                            </div>
                            <div class="progress-bars">
                              <div class="progress-bar">
                                <div class="progress-label">My Performance</div>
                                <div class="progress-track">
                                  <div class="progress-fill my-progress" :style="{ width: (comparison.my_score / comparison.max_mark) * 100 + '%' }"></div>
                                </div>
                              </div>
                              <div class="progress-bar">
                                <div class="progress-label">Class Average</div>
                                <div class="progress-track">
                                  <div class="progress-fill class-progress" :style="{ width: (comparison.avg_score / comparison.max_mark) * 100 + '%' }"></div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div v-else class="loading-comparison">
                          <div class="loading-spinner"></div>
                          <p>Loading comparison data...</p>
                        </div>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading">
      <div class="loading-spinner"></div>
      <p>Loading student data...</p>
    </div>

    <!-- Grade Appeal Dialog -->
    <div v-if="appealDialogVisible" class="dialog-overlay" @click.self="closeAppealDialog">
      <div class="dialog-content">
        <h3>📝 Request Grade Appeal</h3>
        <p><strong>Course:</strong> {{ appealForm.course_name }}</p>
        <form @submit.prevent="submitAppeal">
          <div class="form-group">
            <label for="appeal-reason">Reason for Appeal:</label>
            <textarea id="appeal-reason" 
                      v-model="appealForm.reason" 
                      placeholder="Please provide a clear and detailed reason for your appeal..." 
                      required></textarea>
          </div>
          <div class="dialog-actions">
            <button type="button" class="btn-cancel" @click="closeAppealDialog">Cancel</button>
            <button type="submit" class="btn-submit" :disabled="submittingAppeal">
              {{ submittingAppeal ? 'Submitting...' : 'Submit Appeal' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "StudentsView",
  data() {
    return {
      // User and data
      currentUser: {},
      studentData: null,
      loading: false,
      error: null,
      chartError: null,
      
      // UI state
      expandedCourses: [],
      expandedComparisons: [],
      courseComparisons: {},
      loadingComparison: null,
      
      // Notifications
      showNotifications: false,
      notifications: [],
      notificationInterval: null,
      
      // Appeal system
      appealDialogVisible: false,
      submittingAppeal: false,
      appealForm: {
        course_id: null,
        course_name: '',
        reason: ''
      },
      
      // What-if simulator
      whatIfMode: {},
      whatIfScores: {},
      whatIfTotals: {},
      
      // Chart
      cgpaChartInstance: null,
    };
  },
  computed: {
    unreadNotifications() {
      return this.notifications.filter(n => !n.is_read);
    }
  },
  async mounted() {
    console.log('🔍 StudentsView mounted');
    await this.loadCurrentUser();
    if (this.currentUser.id && this.currentUser.role === "student") {
      await this.fetchStudentData();
      await this.fetchNotifications();
      this.startNotificationPolling();
    } else {
      console.log('❌ User not logged in as student:', this.currentUser);
    }
  },
  beforeUnmount() {
    this.cleanup();
  },
  methods: {
    // --- User Management ---
    async loadCurrentUser() {
      try {
        console.log('🔍 Loading current user from localStorage');
        
        // Try to get user from multiple sources
        const userFromStorage = localStorage.getItem("user");
        const roleFromStorage = localStorage.getItem("role");
        const nameFromStorage = localStorage.getItem("name");
        
        console.log('  userFromStorage:', userFromStorage);
        console.log('  roleFromStorage:', roleFromStorage);
        console.log('  nameFromStorage:', nameFromStorage);
        
        if (userFromStorage) {
          const user = JSON.parse(userFromStorage);
          console.log('  Parsed user:', user);
          
          if (user.role === "student") {
            this.currentUser = user;
            console.log('✅ Student user loaded:', this.currentUser);
          } else {
            console.log('❌ User is not a student:', user.role);
            this.currentUser = {};
          }
        } else if (roleFromStorage === "student" && nameFromStorage) {
          // Fallback: construct user from individual localStorage items
          console.log('  Using fallback method with individual localStorage items');
          this.currentUser = {
            role: roleFromStorage,
            name: nameFromStorage,
            id: localStorage.getItem("user_id") || "temp_id"
          };
          console.log('✅ Student user constructed from localStorage:', this.currentUser);
        } else {
          console.log('❌ No valid student user found in localStorage');
          this.currentUser = {};
        }
      } catch (err) {
        console.error("❌ Error loading user from localStorage:", err);
        this.currentUser = {};
        this.error = "Failed to load user data. Please log in again.";
      }
    },

    // --- Data Fetching ---
    async fetchStudentData() {
      if (!this.currentUser.id) {
        this.error = "No student logged in";
        return;
      }
      
      this.loading = true;
      this.error = null;
      
      try {
        console.log('🔍 Fetching student data for ID:', this.currentUser.id);
        const response = await fetch(`http://localhost:8085/cgpa?student_id=${encodeURIComponent(this.currentUser.id)}`);
        const data = await response.json();
        
        console.log('📊 Student data response:', data);
        
        if (data.success) {
          this.studentData = data;
          this.$nextTick(() => {
            this.createCgpaTrendChart();
          });
        } else {
          this.error = data.message || "Failed to fetch student data";
        }
      } catch (err) {
        console.error("❌ Error fetching student data:", err);
        this.error = "Network error: " + err.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchCourseComparison(courseId) {
      if (!this.currentUser.id) return;
      
      this.loadingComparison = courseId;
      try {
        const response = await fetch(`http://localhost:8085/compare?student_id=${encodeURIComponent(this.currentUser.id)}&course_id=${encodeURIComponent(courseId)}`);
        const data = await response.json();
        
        if (data.success) {
          this.courseComparisons[courseId] = data;
        } else {
          this.error = data.message || "Failed to fetch comparison data";
        }
      } catch (err) {
        console.error("❌ Error fetching course comparison:", err);
        this.error = "Network error: " + err.message;
      } finally {
        this.loadingComparison = null;
      }
    },

    // --- Notifications ---
    async fetchNotifications() {
      if (!this.currentUser.id) return;
      
      try {
        const response = await fetch(`http://localhost:8085/notifications?student_id=${this.currentUser.id}`);
        const data = await response.json();
        
        if (data.success) {
          this.notifications = data.notifications || [];
        }
      } catch (err) {
        console.error("❌ Failed to fetch notifications:", err);
      }
    },

    startNotificationPolling() {
      this.notificationInterval = setInterval(() => {
        this.fetchNotifications();
      }, 60000); // Poll every 60 seconds
    },

    toggleNotifications() {
      this.showNotifications = !this.showNotifications;
    },

    async markAllAsRead() {
      const unreadIds = this.unreadNotifications.map(n => n.id);
      if (unreadIds.length === 0) {
        this.showNotifications = false;
        return;
      }
      
      // Optimistically update UI
      this.notifications.forEach(n => {
        if (unreadIds.includes(n.id)) n.is_read = true;
      });
      this.showNotifications = false;
      
      // Send to server
      try {
        await fetch(`http://localhost:8085/notifications/mark-read`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            student_id: this.currentUser.id, 
            notification_ids: unreadIds 
          })
        });
      } catch (err) {
        console.error("❌ Failed to mark notifications as read:", err);
      }
    },

    // --- Chart Creation ---
    async loadChartJS() {
      return new Promise((resolve, reject) => {
        if (window.Chart) {
          return resolve(window.Chart);
        }
        
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
        script.onload = () => resolve(window.Chart);
        script.onerror = (error) => reject(error);
        document.head.appendChild(script);
      });
    },

    async createCgpaTrendChart() {
      if (!this.studentData || !this.studentData.semesters?.length) {
        console.log('❌ No semester data available for chart');
        return;
      }
      
      try {
        const Chart = await this.loadChartJS();
        const ctx = document.getElementById('cgpaTrendChart')?.getContext('2d');
        
        if (!ctx) {
          console.log('❌ Chart canvas not found');
          return;
        }
        
        // Destroy existing chart
        if (this.cgpaChartInstance) {
          this.cgpaChartInstance.destroy();
        }
        
        const labels = this.studentData.semesters.map(s => `${s.academic_year.slice(-2)} S${s.semester}`);
        const data = this.studentData.semesters.map(s => parseFloat(s.cgpa));
        
        this.cgpaChartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              label: 'CGPA',
              data: data,
              borderColor: '#409EFF',
              backgroundColor: 'rgba(64, 158, 255, 0.1)',
              fill: true,
              tension: 0.3,
              pointBackgroundColor: '#409EFF',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 2,
              pointRadius: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: false,
                min: Math.max(0, Math.min(...data) - 0.5),
                max: 4.0,
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                }
              },
              x: {
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                }
              }
            }
          }
        });
        
        console.log('✅ Chart created successfully');
        this.chartError = null;
      } catch (error) {
        console.error("❌ Failed to create chart:", error);
        this.chartError = "Could not load the performance chart. Please refresh the page.";
      }
    },

    async retryChart() {
      this.chartError = null;
      await this.createCgpaTrendChart();
    },

    // --- Course Details and Comparison ---
    toggleCourseDetails(courseId) {
      const index = this.expandedCourses.indexOf(courseId);
      if (index > -1) {
        this.expandedCourses.splice(index, 1);
      } else {
        this.expandedComparisons = []; // Close comparison when opening details
        this.expandedCourses.push(courseId);
      }
    },

    async toggleCourseComparison(courseId) {
      const index = this.expandedComparisons.indexOf(courseId);
      if (index > -1) {
        this.expandedComparisons.splice(index, 1);
      } else {
        this.expandedCourses = []; // Close details when opening comparison
        this.expandedComparisons.push(courseId);
        
        if (!this.courseComparisons[courseId]) {
          await this.fetchCourseComparison(courseId);
        }
      }
    },

    // --- What-If Simulator ---
    toggleWhatIf(course) {
      const courseId = course.course_id;
      const currentMode = this.whatIfMode[courseId] || false;
      
      if (!currentMode) {
        // Initialize what-if scores with current scores
        this.whatIfScores[courseId] = JSON.parse(JSON.stringify(course.assessment_scores));
        this.updateWhatIfTotal(courseId);
      }
      
      this.whatIfMode = { ...this.whatIfMode, [courseId]: !currentMode };
    },

    updateWhatIfTotal(courseId) {
      if (!this.whatIfScores[courseId]) return;
      
      const total = this.whatIfScores[courseId].reduce((sum, assessment) => {
        const score = Math.max(0, Math.min(assessment.mark_obtained, assessment.max_mark));
        return sum + (score / assessment.max_mark) * assessment.percentage;
      }, 0);
      
      this.whatIfTotals = { ...this.whatIfTotals, [courseId]: total };
    },

    // --- Grade Appeal ---
    openAppealDialog(course) {
      this.appealForm = {
        course_id: course.course_id,
        course_name: course.course_name,
        reason: ''
      };
      this.appealDialogVisible = true;
    },

    closeAppealDialog() {
      this.appealDialogVisible = false;
      this.appealForm = { course_id: null, course_name: '', reason: '' };
    },

    async submitAppeal() {
      if (!this.appealForm.reason.trim()) {
        alert("Please provide a reason for your appeal.");
        return;
      }
      
      this.submittingAppeal = true;
      
      try {
        const response = await fetch('http://localhost:8085/appeals', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            student_id: this.currentUser.id,
            course_id: this.appealForm.course_id,
            reason: this.appealForm.reason
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          alert("✅ Appeal submitted successfully! You will be notified of the outcome.");
          this.closeAppealDialog();
        } else {
          alert(`❌ Failed to submit appeal: ${data.message}`);
        }
      } catch (err) {
        console.error("❌ Error submitting appeal:", err);
        alert("❌ Network error while submitting appeal. Please try again.");
      } finally {
        this.submittingAppeal = false;
      }
    },

    // --- Utility Methods ---
    getInitials(name) {
      return name?.split(' ').map(word => word[0]).join('').toUpperCase() || '??';
    },

    retryFetch() {
      this.error = null;
      this.fetchStudentData();
    },

    redirectToLogin() {
      this.$router.push('/login');
    },

    timeAgo(dateStr) {
      const seconds = Math.floor((new Date() - new Date(dateStr)) / 1000);
      if (seconds < 60) return `just now`;
      const minutes = Math.floor(seconds / 60);
      if (minutes < 60) return `${minutes}m ago`;
      const hours = Math.floor(minutes / 60);
      if (hours < 24) return `${hours}h ago`;
      const days = Math.floor(hours / 24);
      return `${days}d ago`;
    },

    formatComponentType(type) {
      const typeMap = {
        assignment: "Assignment",
        quiz: "Quiz",
        project: "Project",
        lab: "Lab",
        test: "Test",
        presentation: "Presentation"
      };
      return typeMap[type] || `Component (${type})`;
    },

    getGradeFromCGPA(cgpa) {
      const numCgpa = parseFloat(cgpa);
      if (numCgpa >= 4.0) return "A+/A";
      if (numCgpa >= 3.67) return "A-";
      if (numCgpa >= 3.33) return "B+";
      if (numCgpa >= 3.0) return "B";
      if (numCgpa >= 2.67) return "B-";
      if (numCgpa >= 2.33) return "C+";
      if (numCgpa >= 2.0) return "C";
      if (numCgpa >= 1.67) return "C-";
      if (numCgpa >= 1.33) return "D+";
      if (numCgpa >= 1.0) return "D";
      if (numCgpa >= 0.67) return "D-";
      return "E";
    },

    getGradeFromScore(score) {
      if (score >= 90) return 'A+';
      if (score >= 80) return 'A';
      if (score >= 75) return 'A-';
      if (score >= 70) return 'B+';
      if (score >= 65) return 'B';
      if (score >= 60) return 'B-';
      if (score >= 55) return 'C+';
      if (score >= 50) return 'C';
      if (score >= 45) return 'C-';
      if (score >= 40) return 'D';
      return 'E';
    },

    getGradeClass(grade) {
      if (["A+", "A"].includes(grade)) return "grade-a";
      if (["A-", "B+"].includes(grade)) return "grade-b";
      if (["B", "B-"].includes(grade)) return "grade-c";
      if (["C+", "C", "C-"].includes(grade)) return "grade-d";
      return "grade-f";
    },

    getGradeChange(oldGrade, newGrade) {
      if (oldGrade === newGrade) return "No change";
      
      const gradeOrder = ['E', 'D', 'C-', 'C', 'C+', 'B-', 'B', 'B+', 'A-', 'A', 'A+'];
      const oldIndex = gradeOrder.indexOf(oldGrade);
      const newIndex = gradeOrder.indexOf(newGrade);
      
      if (newIndex > oldIndex) return `⬆️ Improved from ${oldGrade}`;
      if (newIndex < oldIndex) return `⬇️ Dropped from ${oldGrade}`;
      return "No change";
    },

    getGradeChangeClass(oldGrade, newGrade) {
      const gradeOrder = ['E', 'D', 'C-', 'C', 'C+', 'B-', 'B', 'B+', 'A-', 'A', 'A+'];
      const oldIndex = gradeOrder.indexOf(oldGrade);
      const newIndex = gradeOrder.indexOf(newGrade);
      
      if (newIndex > oldIndex) return "grade-improvement";
      if (newIndex < oldIndex) return "grade-decline";
      return "grade-same";
    },

    getDifferenceClass(difference) {
      if (difference > 0) return "positive";
      if (difference < 0) return "negative";
      return "neutral";
    },

    getPercentile(rank, total) {
      if (!total) return 0;
      return Math.round(((total - rank + 1) / total) * 100);
    },

    cleanup() {
      if (this.notificationInterval) {
        clearInterval(this.notificationInterval);
      }
      if (this.cgpaChartInstance) {
        this.cgpaChartInstance.destroy();
      }
    }
  }
};
</script>

<style scoped>
/* Base Styles */
.student-dashboard {
  padding: 24px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 32px;
  font-weight: 700;
}

/* Student Info Section */
.student-info {
  background: #ffffff;
  padding: 24px 28px;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
  margin-bottom: 32px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 20px;
}

.profile-pic {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 3px solid #409eff;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.profile-pic:hover {
  transform: scale(1.05);
}

.profile-placeholder {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #409eff 0%, #3d7eff 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: bold;
}

.user-details h2 {
  margin: 0 0 10px 0;
  font-size: 24px;
  font-weight: 600;
  color: #2c3e50;
}

.user-details p {
  margin: 4px 0;
  color: #7f8c8d;
  font-size: 14px;
}

.status {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status.active {
  background: #e3fbe5;
  color: #219653;
  border: 1px solid #b3eec0;
}

/* Notifications */
.header-actions {
  position: relative;
}

.notification-bell {
  font-size: 24px;
  color: #606266;
  cursor: pointer;
  position: relative;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.3s ease;
}

.notification-bell:hover {
  background-color: #f5f7fa;
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background-color: #f56c6c;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.notification-dropdown {
  position: absolute;
  top: 120%;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
  width: 320px;
  z-index: 1000;
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  padding: 12px 16px;
  border-bottom: 1px solid #f0f2f5;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item p {
  margin: 0 0 5px 0;
  font-size: 14px;
  color: #303133;
}

.notification-item small {
  color: #909399;
  font-size: 12px;
}

.notification-item.no-notifs {
  text-align: center;
  color: #909399;
  padding: 20px;
}

.mark-read-btn {
  display: block;
  width: 100%;
  padding: 12px;
  border: none;
  background: #f5f7fa;
  cursor: pointer;
  color: #409EFF;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.mark-read-btn:hover {
  background: #e6f7ff;
}

/* CGPA Card */
.cgpa-card {
  text-align: center;
  background: white;
  padding: 32px;
  border-radius: 16px;
  margin-bottom: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  position: relative;
  overflow: hidden;
}

.cgpa-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.cgpa-card h2 {
  margin: 0 0 20px 0;
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
}

.cgpa-value {
  font-size: 48px;
  font-weight: 700;
  margin: 16px 0;
  color: #409eff;
}

.cgpa-grade {
  font-size: 16px;
  color: #7f8c8d;
  font-weight: 500;
  margin-bottom: 20px;
}

.cgpa-progress .progress-bar {
  width: 200px;
  margin: 0 auto;
}

/* Performance Trend Chart */
.performance-trend-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 32px;
}

.performance-trend-card h3 {
  margin: 0 0 24px 0;
  text-align: center;
  color: #2c3e50;
  font-size: 20px;
  font-weight: 600;
}

.chart-container {
  position: relative;
  height: 250px;
}

.chart-error {
  text-align: center;
  padding: 40px;
  color: #e74c3c;
}

/* Semester Cards */
.semester-card {
  background: white;
  border-radius: 16px;
  margin-bottom: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s ease;
}

.semester-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.semester-header {
  background: white;
  padding: 24px 32px;
  border-bottom: 1px solid #f0f2f5;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.semester-header::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.semester-header h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #2c3e50;
}

.semester-cgpa {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 14px;
}

.semester-cgpa span:first-child {
  color: #7f8c8d;
  font-weight: 500;
}

.semester-cgpa .grade {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 6px 12px;
  border-radius: 12px;
  font-weight: 500;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Tables */
.courses-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 16px 20px;
  text-align: left;
  border-bottom: 1px solid #f0f2f5;
}

th {
  background: #fafbfc;
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.course-code, .course-name {
  font-weight: 500;
  color: #2c3e50;
}

.score {
  font-weight: 600;
  color: #409eff;
  font-size: 14px;
}

.grade {
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 8px;
  text-align: center;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.grade-a { background: linear-gradient(135deg, #00b894 0%, #00a085 100%); color: white; }
.grade-b { background: linear-gradient(135deg, #0984e3 0%, #074f8f 100%); color: white; }
.grade-c { background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%); color: white; }
.grade-d { background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%); color: white; }
.grade-f { background: linear-gradient(135deg, #d63031 0%, #74b9ff 100%); color: white; }

/* Action Buttons */
.actions-cell {
  display: flex;
  gap: 8px;
  align-items: center;
}

.details-btn, .compare-btn, .appeal-btn {
  background: linear-gradient(135deg, #409eff 0%, #3d7eff 100%);
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-width: 70px;
}

.compare-btn {
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
}

.appeal-btn {
  background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
}

.details-btn:hover, .compare-btn:hover, .appeal-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.details-btn:disabled, .compare-btn:disabled, .appeal-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Course Details */
.course-details, .course-comparison {
  background: #fafbfc;
}

.assessment-details, .comparison-details {
  padding: 32px;
}

.assessment-details h4, .comparison-details h4 {
  margin: 0 0 24px 0;
  color: #2c3e50;
  font-size: 18px;
  font-weight: 600;
}

.details-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.what-if-btn {
  background: #1abc9c;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.what-if-btn.active {
  background: #c0392b;
}

.what-if-btn:hover {
  transform: translateY(-2px);
}

.assessments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.assessment-item {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.assessment-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.assessment-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 12px;
  font-size: 16px;
}

.assessment-score {
  font-size: 18px;
  color: #409eff;
  font-weight: 700;
  margin-bottom: 8px;
}

.what-if-input {
  width: 70px;
  padding: 4px 8px;
  text-align: center;
  border: 2px solid #409eff;
  border-radius: 6px;
  margin: 0 5px;
  font-weight: bold;
}

.assessment-remarks {
  margin-top: 15px;
  padding: 12px;
  background: #f9f9f9;
  border-left: 3px solid #667eea;
  border-radius: 4px;
  font-size: 13px;
}

.assessment-remarks strong {
  color: #2c3e50;
}

.assessment-remarks p {
  margin: 5px 0 0 0;
  color: #606266;
  line-height: 1.5;
}

.what-if-result {
  margin-top: 24px;
  padding: 20px;
  background: #eef8ff;
  border-left: 4px solid #409EFF;
  border-radius: 8px;
}

.what-if-result h4 {
  margin: 0 0 12px 0;
  color: #2c3e50;
}

.what-if-result p {
  margin: 8px 0;
  font-size: 16px;
}

.grade-text {
  font-size: 1.2em;
  font-weight: bold;
}

.grade-improvement { color: #00b894; }
.grade-decline { color: #e74c3c; }
.grade-same { color: #7f8c8d; }

/* Comparison Styles */
.comparison-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding-bottom: 20px;
  border-bottom: 2px solid #f0f2f5;
}

.ranking-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.ranking-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 10px 16px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
}

.percentile {
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
  color: white;
  padding: 8px 14px;
  border-radius: 12px;
  font-weight: 500;
  font-size: 12px;
}

.comparison-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
  gap: 24px;
}

.comparison-item {
  background: white;
  padding: 28px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #409eff;
  transition: all 0.3s ease;
}

.comparison-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.component-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 20px;
  font-size: 16px;
}

.scores-comparison {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

.score-item {
  text-align: center;
  background: #fafbfc;
  padding: 16px;
  border-radius: 12px;
}

.score-item label {
  display: block;
  font-size: 12px;
  color: #7f8c8d;
  margin-bottom: 8px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.score-value {
  display: block;
  font-weight: 700;
  font-size: 16px;
  color: #2c3e50;
  margin-bottom: 4px;
}

.score-percentage {
  display: block;
  font-size: 12px;
  color: #7f8c8d;
}

.my-score {
  background: linear-gradient(135deg, #409eff20 0%, #409eff10 100%);
  border: 1px solid #409eff30;
}

.my-score .score-value {
  color: #409eff;
}

.class-avg {
  background: linear-gradient(135deg, #95a5a620 0%, #95a5a610 100%);
  border: 1px solid #95a5a630;
}

.difference-value {
  font-weight: 700;
  font-size: 18px;
  padding: 8px 16px;
  border-radius: 12px;
}

.difference-value.positive {
  color: white;
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
}

.difference-value.negative {
  color: white;
  background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
}

.difference-value.neutral {
  color: #7f8c8d;
  background: #f0f2f5;
}

.progress-bars {
  margin-top: 20px;
}

.progress-bar {
  margin-bottom: 16px;
}

.progress-label {
  font-size: 12px;
  color: #7f8c8d;
  margin-bottom: 8px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.progress-track {
  height: 8px;
  background: #f0f2f5;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
}

.progress-fill {
  height: 100%;
  border-radius: 8px;
  transition: width 0.6s ease;
}

.my-progress {
  background: linear-gradient(90deg, #409eff, #3d7eff);
}

.class-progress {
  background: linear-gradient(90deg, #6c757d, #495057);
}

/* Dialog Styles */
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.dialog-content {
  background: white;
  padding: 28px;
  border-radius: 16px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.dialog-content h3 {
  margin: 0 0 20px 0;
  color: #2c3e50;
  font-size: 20px;
  font-weight: 600;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #2c3e50;
}

.form-group textarea {
  width: 100%;
  min-height: 120px;
  padding: 12px;
  border-radius: 8px;
  border: 2px solid #e0e6ed;
  font-family: inherit;
  font-size: 14px;
  resize: vertical;
  transition: border-color 0.3s ease;
}

.form-group textarea:focus {
  outline: none;
  border-color: #409eff;
}

.dialog-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-cancel, .btn-submit {
  padding: 12px 24px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-cancel {
  background: #f5f7fa;
  color: #606266;
}

.btn-cancel:hover {
  background: #e4e7ed;
}

.btn-submit {
  background: #409EFF;
  color: white;
}

.btn-submit:hover {
  background: #337ecc;
  transform: translateY(-2px);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Utility Styles */
.error-message {
  background: #fef0f0;
  color: #f56c6c;
  padding: 16px 20px;
  border-radius: 12px;
  margin-bottom: 24px;
  border: 1px solid #fbc4c4;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.retry-btn, .login-btn {
  background: #409eff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.retry-btn:hover, .login-btn:hover {
  background: #337ecc;
  transform: translateY(-1px);
}

.login-required {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.login-required p {
  font-size: 18px;
  margin-bottom: 20px;
}

.loading, .loading-comparison {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #409eff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .student-dashboard {
    padding: 16px;
  }
  
  .header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .user-profile {
    flex-direction: column;
    text-align: center;
  }
  
  .semester-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .scores-comparison {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .comparison-grid {
    grid-template-columns: 1fr;
  }
  
  .assessments-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-cell {
    flex-direction: column;
    gap: 4px;
  }
  
  .actions-cell button {
    font-size: 11px;
    padding: 6px 8px;
  }
  
  th, td {
    padding: 8px 12px;
    font-size: 13px;
  }
  
  .cgpa-value {
    font-size: 36px;
  }
  
  .notification-dropdown {
    width: 280px;
    right: -20px;
  }
}
</style>