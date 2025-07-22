<template>
  <div class="dashboard-container">
    <!-- Welcome Section -->
    <el-card class="welcome-card" :class="roleClass">
      <div class="welcome-content">
        <div class="welcome-text">
          <h2 class="welcome-title">{{ welcomeMessage }} 🎓</h2>
          <p class="welcome-description">{{ roleDescription }}</p>
          <div class="user-info">
            <el-tag :type="roleTagType" size="large">{{ userRole.toUpperCase() }}</el-tag>
            <span class="user-name">{{ userName }}</span>
          </div>
        </div>
        <div class="welcome-icon">{{ roleIcon }}</div>
      </div>
    </el-card>

    <!-- Quick Actions Section -->
    <el-card class="quick-actions-card" v-if="quickActions.length > 0">
      <h3 class="section-title">⚡ Quick Actions</h3>
      <div class="actions-grid">
        <el-button
          v-for="action in quickActions"
          :key="action.id"
          :type="action.type"
          :icon="action.icon"
          size="large"
          class="action-button"
          @click="handleQuickAction(action.route)"
        >
          {{ action.label }}
        </el-button>
      </div>
    </el-card>

    <!-- Role-Specific Dashboard Content -->
    <div class="dashboard-content">
      <!-- Lecturer Dashboard -->
      <template v-if="userRole === 'lecturer'">
        <div class="dashboard-grid">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">📚</div>
              <div class="stat-info">
                <h4>Courses</h4>
                <p class="stat-number">{{ stats.courses || 0 }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">👥</div>
              <div class="stat-info">
                <h4>Students</h4>
                <p class="stat-number">{{ stats.students || 0 }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">📝</div>
              <div class="stat-info">
                <h4>Assessments</h4>
                <p class="stat-number">{{ stats.assessments || 0 }}</p>
              </div>
            </div>
          </el-card>
        </div>

        <el-card class="feature-card">
          <h3>📊 Lecturer Tools</h3>
          <div class="features-grid">
            <div class="feature-item" @click="navigateTo('/course-management')">
              <el-icon size="30"><Document /></el-icon>
              <h4>Course Management</h4>
              <p>Manage courses and student enrollment</p>
            </div>
            <div class="feature-item" @click="navigateTo('/ca-components')">
              <el-icon size="30"><EditPen /></el-icon>
              <h4>Assessment Components</h4>
              <p>Create and manage assessments</p>
            </div>
            <div class="feature-item" @click="navigateTo('/lecturer-results')">
              <el-icon size="30"><TrendCharts /></el-icon>
              <h4>View Results</h4>
              <p>Review student performance</p>
            </div>
          </div>
        </el-card>
      </template>

      <!-- Student Dashboard -->
      <template v-if="userRole === 'student'">
        <div class="dashboard-grid">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">📚</div>
              <div class="stat-info">
                <h4>Enrolled Courses</h4>
                <p class="stat-number">{{ stats.enrolledCourses || 0 }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">📈</div>
              <div class="stat-info">
                <h4>Current CGPA</h4>
                <p class="stat-number">{{ stats.cgpa || '0.00' }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">🏆</div>
              <div class="stat-info">
                <h4>Class Rank</h4>
                <p class="stat-number">{{ stats.rank || 'N/A' }}</p>
              </div>
            </div>
          </el-card>
        </div>

        <el-card class="feature-card">
          <h3>🎯 Student Tools</h3>
          <div class="features-grid">
            <div class="feature-item" @click="navigateTo('/student-dashboard')">
              <el-icon size="30"><Document /></el-icon>
              <h4>My Grades</h4>
              <p>View results and performance</p>
            </div>
            <div class="feature-item" @click="navigateTo('/cgpa-calculator')">
              <el-icon size="30"><Calculator /></el-icon>
              <h4>CGPA Calculator</h4>
              <p>Calculate and track your CGPA</p>
            </div>
            <div class="feature-item" @click="navigateTo('/student-dashboard')">
              <el-icon size="30"><TrendCharts /></el-icon>
              <h4>Compare Performance</h4>
              <p>Compare with classmates</p>
            </div>
          </div>
        </el-card>
      </template>

      <!-- ✅ FIXED: Academic Advisor Dashboard -->
      <template v-if="userRole === 'academic advisor'">
        <div class="dashboard-grid">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">👥</div>
              <div class="stat-info">
                <h4>Advisees</h4>
                <p class="stat-number">{{ stats.advisees || 0 }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">⚠️</div>
              <div class="stat-info">
                <h4>At-Risk Students</h4>
                <p class="stat-number text-warning">{{ stats.atRiskStudents || 0 }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">📅</div>
              <div class="stat-info">
                <h4>Meetings This Month</h4>
                <p class="stat-number">{{ stats.meetings || 0 }}</p>
              </div>
            </div>
          </el-card>
        </div>

        <el-card class="feature-card">
          <h3>🛡️ Advisor Tools</h3>
          <div class="features-grid">
            <div class="feature-item" @click="navigateTo('/advisorworkspace')">
              <el-icon size="30"><User /></el-icon>
              <h4>Manage Advisees</h4>
              <p>Monitor student progress</p>
            </div>
            <div class="feature-item" @click="navigateTo('/advisorworkspace')">
              <el-icon size="30"><Document /></el-icon>
              <h4>Generate Reports</h4>
              <p>Create consultation reports</p>
            </div>
            <div class="feature-item" @click="navigateTo('/advisorworkspace')">
              <el-icon size="30"><Warning /></el-icon>
              <h4>At-Risk Students</h4>
              <p>Identify struggling students</p>
            </div>
          </div>
        </el-card>
      </template>

      <!-- Admin Dashboard -->
      <template v-if="userRole === 'admin'">
        <div class="dashboard-grid">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">👤</div>
              <div class="stat-info">
                <h4>Total Users</h4>
                <p class="stat-number">{{ stats.totalUsers || 0 }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">📚</div>
              <div class="stat-info">
                <h4>Total Courses</h4>
                <p class="stat-number">{{ stats.totalCourses || 0 }}</p>
              </div>
            </div>
          </el-card>
          
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">📊</div>
              <div class="stat-info">
                <h4>System Health</h4>
                <p class="stat-number text-success">Good</p>
              </div>
            </div>
          </el-card>
        </div>

        <el-card class="feature-card">
          <h3>🔧 Admin Tools</h3>
          <div class="features-grid">
            <div class="feature-item" @click="navigateTo('/admin/user-management')">
              <el-icon size="30"><User /></el-icon>
              <h4>User Management</h4>
              <p>Manage all system users</p>
            </div>
            <div class="feature-item" @click="navigateTo('/course-management')">
              <el-icon size="30"><Document /></el-icon>
              <h4>Course Management</h4>
              <p>Manage courses and assignments</p>
            </div>
            <div class="feature-item" @click="navigateTo('/system-logs')">
              <el-icon size="30"><Monitor /></el-icon>
              <h4>System Logs</h4>
              <p>Monitor system activity</p>
            </div>
          </div>
        </el-card>
      </template>
    </div>

    <!-- Recent Activity Section -->
    <el-card class="activity-card" v-if="recentActivities.length > 0">
      <h3 class="section-title">📈 Recent Activity</h3>
      <el-timeline>
        <el-timeline-item
          v-for="activity in recentActivities"
          :key="activity.id"
          :timestamp="activity.timestamp"
          :type="activity.type"
        >
          {{ activity.description }}
        </el-timeline-item>
      </el-timeline>
    </el-card>

    <!-- Debug Panel (Development Only) -->
    <el-card class="debug-card" v-if="showDebug">
      <h3>🐛 Debug Panel (Development)</h3>
      <p><strong>Current Role:</strong> {{ userRole }}</p>
      <p><strong>Current User:</strong> {{ userName }}</p>
      <p><strong>Role from localStorage:</strong> {{ localStorage.getItem('role') }}</p>
      <p><strong>Name from localStorage:</strong> {{ localStorage.getItem('name') }}</p>
      <div class="debug-buttons">
        <el-button size="small" @click="switchRole('lecturer')">Switch to Lecturer</el-button>
        <el-button size="small" @click="switchRole('student')">Switch to Student</el-button>
        <el-button size="small" @click="switchRole('academic advisor')">Switch to Advisor</el-button>
        <el-button size="small" @click="switchRole('admin')">Switch to Admin</el-button>
      </div>
      <el-button size="small" type="danger" @click="clearStorage">Clear Storage & Logout</el-button>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { 
  Document, 
  EditPen, 
  TrendCharts, 
  Calculator, 
  User, 
  Warning, 
  Monitor,
  View
} from '@element-plus/icons-vue'

const router = useRouter()

// --- Reactive State ---
const userRole = ref('student') // Default role
const userName = ref('Guest')
const stats = ref({})
const recentActivities = ref([])
// Show debug panel only in development mode
const showDebug = ref(process.env.NODE_ENV === 'development')

// --- ✅ FIXED: Computed Properties for Dynamic UI ---
const welcomeMessage = computed(() => {
  const messages = {
    lecturer: `Welcome back, ${userName.value}!`,
    student: `Hello, ${userName.value}!`,
    'academic advisor': `Good day, ${userName.value}!`,  // ✅ Fixed
    admin: `Welcome, Administrator!`
  }
  return messages[userRole.value] || 'Welcome!'
})

const roleDescription = computed(() => {
  const descriptions = {
    lecturer: 'Manage your courses, assessments, and track student performance.',
    student: 'View your academic progress, compare performance, and track your CGPA.',
    'academic advisor': 'Monitor your advisees\' progress and provide academic guidance.',  // ✅ Fixed
    admin: 'Manage system users, courses, and monitor overall system health.'
  }
  return descriptions[userRole.value] || 'Welcome to the Course Marking System'
})

const roleIcon = computed(() => ({
  lecturer: '👨‍🏫', 
  student: '🎓', 
  'academic advisor': '🛡️',  // ✅ Fixed
  admin: '⚙️'
}[userRole.value] || '👤'))

const roleClass = computed(() => {
  // Convert role to safe CSS class name
  const roleMap = {
    'academic advisor': 'advisor',
    'lecturer': 'lecturer',
    'student': 'student',
    'admin': 'admin'
  }
  return `welcome-${roleMap[userRole.value] || 'default'}`
})

const roleTagType = computed(() => ({
  lecturer: 'primary', 
  student: 'success', 
  'academic advisor': 'warning',  // ✅ Fixed
  admin: 'danger'
}[userRole.value] || 'info'))

const quickActions = computed(() => {
  const actions = {
    lecturer: [
      { id: 1, label: 'Add Assessment', type: 'primary', icon: EditPen, route: '/ca-components' },
      { id: 2, label: 'View Results', type: 'success', icon: View, route: '/lecturer-results' },
    ],
    student: [
      { id: 1, label: 'View My Grades', type: 'primary', icon: View, route: '/student-dashboard' },
      { id: 2, label: 'Calculate CGPA', type: 'success', icon: Calculator, route: '/cgpa-calculator' },
    ],
    'academic advisor': [  // ✅ Fixed
      { id: 1, label: 'View Advisees', type: 'primary', icon: User, route: '/advisorworkspace' },
      { id: 2, label: 'Add Meeting Note', type: 'success', icon: EditPen, route: '/advisorworkspace' },
    ],
    admin: [
      { id: 1, label: 'Manage Users', type: 'primary', icon: User, route: '/admin/user-management' },
      { id: 2, label: 'System Logs', type: 'warning', icon: Monitor, route: '/system-logs' },
    ]
  }
  return actions[userRole.value] || []
})

// --- Methods ---
const navigateTo = (route) => {
  router.push(route)
}

const handleQuickAction = (route) => {
  if (route) {
    navigateTo(route)
  } else {
    ElMessage.info('Feature coming soon!')
  }
}

const fetchUserStats = async () => {
  try {
    // MOCK DATA - Replace with actual API calls per role
    const mockStats = {
      lecturer: { courses: 5, students: 120, assessments: 25 },
      student: { enrolledCourses: 6, cgpa: '3.75', rank: '5th' },
      'academic advisor': { advisees: 30, atRiskStudents: 4, meetings: 8 },  // ✅ Fixed
      admin: { totalUsers: 250, totalCourses: 45 }
    };
    stats.value = mockStats[userRole.value] || {};
  } catch (error) {
    console.error('Error fetching stats:', error);
    stats.value = {}; // Reset on error
  }
}

const fetchRecentActivities = async () => {
  try {
    // MOCK DATA - Replace with actual API call
    recentActivities.value = [
      { id: 1, description: 'Updated assessment scores for CS101', timestamp: '2025-06-25 10:30', type: 'primary' },
      { id: 2, description: 'New student enrolled in course', timestamp: '2025-06-25 09:15', type: 'success' },
    ];
  } catch (error) { console.error('Error fetching activities:', error); }
}

// --- ✅ FIXED: Debug Methods (for development only) ---
const switchRole = (newRole) => {
  localStorage.setItem('role', newRole)
  userRole.value = newRole
  
  // Mock user names for different roles  
  const names = { 
    lecturer: 'Dr. Alan', 
    student: 'Jane Doe', 
    'academic advisor': 'Mrs. Smith',  // ✅ Fixed to match database
    admin: 'Admin' 
  };
  
  localStorage.setItem('name', names[newRole]);
  userName.value = names[newRole];
  
  fetchUserStats()
  ElMessage.success(`Switched to ${newRole} role`)
}

const clearStorage = () => {
  localStorage.clear()
  ElMessage.warning('Storage cleared - please login again')
  router.push('/login')
}

// --- ✅ ENHANCED: Lifecycle Hook with Better Debugging ---
onMounted(() => {
  console.log('🔍 DASHBOARD onMounted DEBUG:');
  
  // Load user info from localStorage on component mount
  const storedRole = localStorage.getItem('role');
  const storedName = localStorage.getItem('name');
  const storedUser = localStorage.getItem('user');
  
  console.log('  storedRole from localStorage:', storedRole);
  console.log('  storedName from localStorage:', storedName);
  console.log('  storedUser from localStorage:', storedUser);
  console.log('  Current route path:', router.currentRoute.value.path);
  console.log('  userRole.value before:', userRole.value);
  
  if (storedRole) {
    userRole.value = storedRole;
    console.log('  userRole.value after setting:', userRole.value);
  }
  if (storedName) {
    userName.value = storedName;
  }
  
  // Additional debugging for parsed user data
  if (storedUser) {
    try {
      const parsedUser = JSON.parse(storedUser);
      console.log('  Parsed user object:', parsedUser);
      console.log('  Parsed user role:', parsedUser.role);
    } catch (e) {
      console.error('  Error parsing stored user:', e);
    }
  }
  
  // Check what template should render
  console.log('  Should show student template?:', userRole.value === 'student');
  console.log('  Should show lecturer template?:', userRole.value === 'lecturer');
  console.log('  Should show advisor template?:', userRole.value === 'academic advisor');
  console.log('  Should show admin template?:', userRole.value === 'admin');
  
  fetchUserStats();
  fetchRecentActivities();
})
</script>

<style scoped>
.dashboard-container {
  padding: 24px;
  background: #f5f7fa;
  min-height: 100vh;
}

.welcome-card {
  margin-bottom: 24px;
  border-radius: 16px;
  border: none;
  overflow: hidden;
  color: white;
}

/* Role-specific background gradients */
.welcome-lecturer { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.welcome-student { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.welcome-advisor { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.welcome-admin { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.welcome-default { background: linear-gradient(135deg, #8e9eab 0%, #eef2f3 100%); }

.welcome-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
}

.welcome-text { flex: 1; }
.welcome-title { font-size: 28px; font-weight: bold; margin: 0 0 8px 0; }
.welcome-description { font-size: 16px; margin: 0 0 16px 0; opacity: 0.9; }
.user-info { display: flex; align-items: center; gap: 12px; }
.user-name { font-weight: 600; font-size: 18px; }
.welcome-icon { font-size: 64px; opacity: 0.8; }

.quick-actions-card { margin-bottom: 24px; border-radius: 12px; }
.section-title { margin: 0 0 16px 0; color: #333; font-size: 20px; font-weight: 600; }
.actions-grid { display: flex; gap: 12px; flex-wrap: wrap; }
.action-button { flex: 1; min-width: 160px; }

.dashboard-content { margin-bottom: 24px; }
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }

.stat-card { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); }
.stat-content { display: flex; align-items: center; gap: 16px; padding: 16px; }
.stat-icon { font-size: 32px; background: #f0f2f5; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.stat-info h4 { margin: 0 0 4px 0; color: #666; font-size: 14px; font-weight: 500; }
.stat-number { margin: 0; font-size: 24px; font-weight: bold; color: #333; }
.text-warning { color: #e6a23c; }
.text-success { color: #67c23a; }

.feature-card { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); }
.feature-card h3 { margin: 0 0 20px 0; color: #333; font-size: 20px; font-weight: 600; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
.feature-item { padding: 20px; border: 1px solid #e9ecef; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: white; }
.feature-item:hover { border-color: #409eff; box-shadow: 0 4px 16px rgba(64, 158, 255, 0.1); transform: translateY(-2px); }
.feature-item h4 { margin: 12px 0 8px 0; color: #333; font-size: 16px; font-weight: 600; }
.feature-item p { margin: 0; color: #666; font-size: 14px; }

.activity-card { border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); }

.debug-card { margin-top: 24px; border: 2px solid #f56c6c; border-radius: 12px; background: #fef0f0; }
.debug-card h3 { color: #f56c6c; margin: 0 0 16px 0; }
.debug-buttons { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }

@media (max-width: 768px) {
  .dashboard-container { padding: 16px; }
  .welcome-content { flex-direction: column; text-align: center; gap: 16px; }
  .welcome-icon { font-size: 48px; }
  .actions-grid { flex-direction: column; }
  .action-button { width: 100%; }
  .dashboard-grid, .features-grid { grid-template-columns: 1fr; }
}
</style>