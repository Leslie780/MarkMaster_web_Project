<template>
  <div class="student-dashboard">
    <div class="header">
      <h1>Student Dashboard</h1>
      <div class="student-info" v-if="currentUser.name">
        <div class="user-profile">
          <img
            v-if="currentUser.profile_pic"
            :src="currentUser.profile_pic"
            :alt="currentUser.name"
            class="profile-pic"
          />
          <div class="user-details">
            <h2>{{ currentUser.name }}</h2>
            <p>Matric No: {{ currentUser.matric_no }}</p>
            <p>Email: {{ currentUser.email }}</p>
            <p>
              Status:
              <span :class="['status', currentUser.status]">
                {{ currentUser.status }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      <p>{{ error }}</p>
    </div>

    <div v-if="!currentUser.id && !loading" class="login-required">
      <p>Please log in as a student to view your dashboard.</p>
    </div>

    <div v-if="studentData && !loading" class="dashboard-content">
      <div class="cgpa-card">
        <h2>Overall CGPA</h2>
        <div class="cgpa-value">{{ studentData.overall_cgpa }}</div>
        <div class="cgpa-grade">
          {{ getGradeFromCGPA(studentData.overall_cgpa) }}
        </div>
      </div>

      <div class="semesters-container">
        <div
          v-for="semester in studentData.semesters"
          :key="`${semester.academic_year}-${semester.semester}`"
          class="semester-card"
        >
          <div class="semester-header">
            <h3>{{ semester.academic_year }} - {{ semester.semester }}</h3>
            <div class="semester-cgpa">
              <span>Semester CGPA: {{ semester.cgpa }}</span>
              <span class="grade">{{ getGradeFromCGPA(semester.cgpa) }}</span>
            </div>
          </div>

          <div class="courses-table">
            <table>
              <thead>
                <tr>
                  <th>Course Name</th>
                  <th>Credit Hours</th>
                  <th>Total Score</th>
                  <th>Final Exam</th>
                  <th>Grade</th>
                  <th>Grade Point</th>
                  <th>Details</th>
                  <th>Compare</th>
                </tr>
              </thead>
              <tbody>
                <template
                  v-for="course in semester.courses"
                  :key="course.course_id"
                >
                  <tr>
                    <td class="course-name">{{ course.course_name }}</td>
                    <td>{{ course.credit_hours }}</td>
                    <td class="score">{{ course.total_score }}%</td>
                    <td class="score">{{ course.final_exam_score }}%</td>
                    <td :class="['grade', getGradeClass(course.grade)]">
                      {{ course.grade }}
                    </td>
                    <td>{{ course.grade_point }}</td>
                    <td>
                      <button
                        @click="toggleCourseDetails(course.course_id)"
                        class="details-btn"
                      >
                        {{
                          expandedCourses.includes(course.course_id)
                            ? "Hide"
                            : "Show"
                        }}
                      </button>
                    </td>
                    <td>
                      <button
                        @click="toggleCourseComparison(course.course_id)"
                        class="compare-btn"
                        :disabled="loadingComparison === course.course_id"
                      >
                        {{
                          loadingComparison === course.course_id
                            ? "Loading..."
                            : expandedComparisons.includes(course.course_id)
                            ? "Hide Compare"
                            : "Compare"
                        }}
                      </button>
                    </td>
                  </tr>

                  <!-- Course Details Row -->
                  <tr
                    v-if="expandedCourses.includes(course.course_id)"
                    :key="`details-${course.course_id}`"
                    class="course-details"
                  >
                    <td colspan="8">
                      <div class="assessment-details">
                        <h4>Assessment Score Details</h4>
                        <div class="assessments-grid">
                          <div
                            v-for="assessment in course.assessment_scores"
                            :key="assessment.component_id"
                            class="assessment-item"
                          >
                            <div class="assessment-name">
                              {{
                                assessment.title ||
                                formatComponentType(assessment.type)
                              }}
                            </div>
                            <div class="assessment-score">
                              {{ assessment.mark_obtained }} /
                              {{ assessment.max_mark }} ({{
                                assessment.percentage
                              }}%)
                            </div>
                            <div class="assessment-percentage">
                              Score Rate:
                              {{
                                (
                                  (assessment.mark_obtained /
                                    assessment.max_mark) *
                                  100
                                ).toFixed(1)
                              }}%
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>

                  <!-- Course Comparison Row -->
                  <tr
                    v-if="expandedComparisons.includes(course.course_id)"
                    :key="`comparison-${course.course_id}`"
                    class="course-comparison"
                  >
                    <td colspan="8">
                      <div class="comparison-details">
                        <div class="comparison-header">
                          <h4>Course Performance Comparison</h4>
                          <div
                            v-if="courseComparisons[course.course_id]"
                            class="ranking-info"
                          >
                            <span class="ranking-badge">
                              Rank:
                              {{ courseComparisons[course.course_id].ranking }}
                              /
                              {{
                                courseComparisons[course.course_id]
                                  .total_students
                              }}
                            </span>
                            <span class="percentile">
                              Top
                              {{
                                getPercentile(
                                  courseComparisons[course.course_id].ranking,
                                  courseComparisons[course.course_id]
                                    .total_students
                                )
                              }}%
                            </span>
                          </div>
                        </div>

                        <div
                          v-if="courseComparisons[course.course_id]"
                          class="comparison-grid"
                        >
                          <div
                            v-for="comparison in courseComparisons[
                              course.course_id
                            ].comparisons"
                            :key="comparison.component_id"
                            class="comparison-item"
                          >
                            <div class="component-name">
                              {{
                                comparison.title ||
                                formatComponentType(comparison.type)
                              }}
                            </div>
                            <div class="scores-comparison">
                              <div class="score-item my-score">
                                <label>My Score:</label>
                                <span class="score-value"
                                  >{{ comparison.my_score }} /
                                  {{ comparison.max_mark }}</span
                                >
                                <span class="score-percentage"
                                  >({{
                                    (
                                      (comparison.my_score /
                                        comparison.max_mark) *
                                      100
                                    ).toFixed(1)
                                  }}%)</span
                                >
                              </div>
                              <div class="score-item class-avg">
                                <label>Class Average:</label>
                                <span class="score-value"
                                  >{{ comparison.avg_score }} /
                                  {{ comparison.max_mark }}</span
                                >
                                <span class="score-percentage"
                                  >({{
                                    (
                                      (comparison.avg_score /
                                        comparison.max_mark) *
                                      100
                                    ).toFixed(1)
                                  }}%)</span
                                >
                              </div>
                              <div class="score-item difference">
                                <label>Difference:</label>
                                <span
                                  :class="[
                                    'difference-value',
                                    getDifferenceClass(
                                      comparison.my_score - comparison.avg_score
                                    ),
                                  ]"
                                >
                                  {{
                                    comparison.my_score > comparison.avg_score
                                      ? "+"
                                      : ""
                                  }}{{
                                    (
                                      comparison.my_score - comparison.avg_score
                                    ).toFixed(1)
                                  }}
                                </span>
                              </div>
                            </div>
                            <div class="progress-bars">
                              <div class="progress-bar">
                                <div class="progress-label">My Performance</div>
                                <div class="progress-track">
                                  <div
                                    class="progress-fill my-progress"
                                    :style="{
                                      width:
                                        (comparison.my_score /
                                          comparison.max_mark) *
                                          100 +
                                        '%',
                                    }"
                                  ></div>
                                </div>
                              </div>
                              <div class="progress-bar">
                                <div class="progress-label">Class Average</div>
                                <div class="progress-track">
                                  <div
                                    class="progress-fill class-progress"
                                    :style="{
                                      width:
                                        (comparison.avg_score /
                                          comparison.max_mark) *
                                          100 +
                                        '%',
                                    }"
                                  ></div>
                                </div>
                              </div>
                            </div>
                          </div>
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

    <div v-if="loading" class="loading">
      <div class="loading-spinner"></div>
      <p>Loading student data...</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "StudentDashboard",
  data() {
    return {
      currentUser: {},
      studentData: null,
      loading: false,
      error: null,
      expandedCourses: [],
      expandedComparisons: [],
      courseComparisons: {},
      loadingComparison: null,
    };
  },
  async mounted() {
    await this.loadCurrentUser();
    if (this.currentUser.id && this.currentUser.role === "student") {
      await this.fetchStudentData();
    }
  },
  methods: {
    async loadCurrentUser() {
      try {
        const user = JSON.parse(localStorage.getItem("user") || "{}");
        this.currentUser = user.role === "student" ? user : {};
      } catch (err) {
        console.error("Error loading user from localStorage:", err);
        this.currentUser = {};
      }
    },

    async fetchStudentData() {
      if (!this.currentUser.id) {
        this.error = "No student logged in";
        return;
      }

      this.loading = true;
      this.error = null;
      this.studentData = null;

      try {
        const response = await fetch(
          `http://localhost:8085/cgpa?student_id=${encodeURIComponent(
            this.currentUser.id
          )}`
        );
        const data = await response.json();

        if (data.success) {
          this.studentData = data;
          this.expandedCourses = [];
          this.expandedComparisons = [];
          this.courseComparisons = {};
        } else {
          this.error = data.message || "Failed to fetch grade data";
        }
      } catch (err) {
        this.error = "Network error: " + err.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchCourseComparison(courseId) {
      if (!this.currentUser.id) {
        return;
      }

      this.loadingComparison = courseId;

      try {
        const response = await fetch(
          `http://localhost:8085/compare?student_id=${encodeURIComponent(
            this.currentUser.id
          )}&course_id=${encodeURIComponent(courseId)}`
        );
        const data = await response.json();

        if (data.success) {
          this.courseComparisons[courseId] = data;
        } else {
          this.error = data.message || "Failed to fetch comparison data";
        }
      } catch (err) {
        this.error = "Network error: " + err.message;
      } finally {
        this.loadingComparison = null;
      }
    },

    async toggleCourseComparison(courseId) {
      const index = this.expandedComparisons.indexOf(courseId);
      if (index > -1) {
        this.expandedComparisons.splice(index, 1);
      } else {
        this.expandedComparisons.push(courseId);

        // Fetch comparison data if not already loaded
        if (!this.courseComparisons[courseId]) {
          await this.fetchCourseComparison(courseId);
        }
      }
    },

    formatComponentType(type) {
      const typeMap = {
        assignment: "Assignment",
        quiz: "Quiz",
        project: "Project",
        lab: "Lab",
        test: "Test",
        presentation: "Presentation",
      };
      return typeMap[type] || `Component (${type})`;
    },

    getGradeFromCGPA(cgpa) {
      if (cgpa >= 4.0) return "A+/A";
      if (cgpa >= 3.67) return "A-";
      if (cgpa >= 3.33) return "B+";
      if (cgpa >= 3.0) return "B";
      if (cgpa >= 2.67) return "B-";
      if (cgpa >= 2.33) return "C+";
      if (cgpa >= 2.0) return "C";
      if (cgpa >= 1.67) return "C-";
      if (cgpa >= 1.33) return "D+";
      if (cgpa >= 1.0) return "D";
      if (cgpa >= 0.67) return "D-";
      return "E";
    },

    getGradeClass(grade) {
      if (["A+", "A"].includes(grade)) return "grade-a";
      if (["A-", "B+"].includes(grade)) return "grade-b";
      if (["B", "B-"].includes(grade)) return "grade-c";
      if (["C+", "C", "C-"].includes(grade)) return "grade-d";
      return "grade-f";
    },

    getDifferenceClass(difference) {
      if (difference > 0) return "positive";
      if (difference < 0) return "negative";
      return "neutral";
    },

    getPercentile(rank, total) {
      return Math.round(((total - rank) / total) * 100);
    },

    toggleCourseDetails(courseId) {
      const index = this.expandedCourses.indexOf(courseId);
      if (index > -1) {
        this.expandedCourses.splice(index, 1);
      } else {
        this.expandedCourses.push(courseId);
      }
    },
  },
};
</script>

<style scoped>
.student-dashboard {
  padding: 24px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.header {
  margin-bottom: 32px;
}

/* 卡片样式优化 */
.student-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  padding: 24px 28px;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
  gap: 20px;
}

/* 个人信息标题样式 */
.student-info h1 {
  font-size: 32px;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 20px;
  flex: 1;
}

/* 头像优化：hover 动画 */
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

/* 文字信息 */
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

/* 状态标记 */
.status {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: inline-block;
  line-height: 1;
}

/* active 状态改为淡绿 */
.status.active {
  background: #e3fbe5;
  color: #219653;
  border: 1px solid #b3eec0;
}

/* 按钮优化 */
.student-info button {
  height: 44px;
  padding: 0 20px;
  border-radius: 10px;
  font-weight: 500;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  letter-spacing: 0.3px;
}

.student-info button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
  filter: brightness(1.05);
}

.student-info button:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
  filter: none;
}

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
}

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

.courses-table {
  overflow-x: auto;
}

table {
  table-layout: fixed;
  width: 100%;
  border-collapse: collapse;
}

th,
td {
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

.course-name {
  max-width: 200px; /* 可根据需要调整 */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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

.grade-a {
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
  color: white;
}
.grade-b {
  background: linear-gradient(135deg, #0984e3 0%, #074f8f 100%);
  color: white;
}
.grade-c {
  background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
  color: white;
}
.grade-d {
  background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
  color: white;
}
.grade-f {
  background: linear-gradient(135deg, #d63031 0%, #74b9ff 100%);
  color: white;
}

.details-btn,
.compare-btn {
  background: linear-gradient(135deg, #409eff 0%, #3d7eff 100%);
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.details-btn:hover,
.compare-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(64, 158, 255, 0.3);
}

.compare-btn {
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
  margin-left: 8px;
}

.compare-btn:hover {
  box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
}

.compare-btn:disabled {
  background: #95a5a6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.course-details,
.course-comparison {
  background: #fafbfc;
}

.assessment-details,
.comparison-details {
  padding: 32px;
}

.assessment-details h4,
.comparison-details h4 {
  margin: 0 0 24px 0;
  color: #2c3e50;
  font-size: 18px;
  font-weight: 600;
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

.assessment-percentage {
  color: #7f8c8d;
  font-size: 14px;
  font-weight: 500;
}

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
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.percentile {
  background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
  color: white;
  padding: 8px 14px;
  border-radius: 12px;
  font-weight: 500;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
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
  text-transform: uppercase;
  letter-spacing: 0.5px;
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
  position: relative;
}

.my-progress {
  background: linear-gradient(90deg, #409eff, #3d7eff);
}

.class-progress {
  background: linear-gradient(90deg, #95a5a6, #7f8c8d);
}

.error-message {
  background: white;
  color: #fd79a8;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 24px;
  border-left: 4px solid #fd79a8;
  box-shadow: 0 2px 12px rgba(253, 121, 168, 0.1);
}

.login-required {
  text-align: center;
  padding: 80px 20px;
  color: #7f8c8d;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.loading {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f0f2f5;
  border-top: 4px solid #409eff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 24px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* 响应式设计 */
@media (max-width: 768px) {
  .student-dashboard {
    padding: 16px;
  }

  .student-info {
    flex-direction: column;
    gap: 24px;
    text-align: center;
  }

  .comparison-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
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

  .semester-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }

  th,
  td {
    padding: 12px 8px;
    font-size: 13px;
  }

  .details-btn,
  .compare-btn {
    padding: 6px 12px;
    font-size: 11px;
  }
}

.class-progress {
  background: linear-gradient(90deg, #6c757d, #495057);
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  border: 1px solid #f5c6cb;
}

.login-required {
  text-align: center;
  padding: 50px;
  color: #6c757d;
}

.loading {
  text-align: center;
  padding: 50px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .student-info {
    flex-direction: column;
    align-items: flex-start;
  }

  .comparison-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .scores-comparison {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .comparison-grid {
    grid-template-columns: 1fr;
  }

  .assessments-grid {
    grid-template-columns: 1fr;
  }
}
</style>
