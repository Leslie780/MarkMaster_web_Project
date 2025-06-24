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
            <!-- Debug Panel (Development Only) -->
    <el-card class="debug-card" v-if="showDebug">
      <h3>🐛 Debug Panel (Development)</h3>
      <p><strong>Current Role:</strong> {{ userRole }}</p>
      <p><strong>Current User:</strong> {{ userName }}</p>
      <div class="debug-buttons">
        <el-button size="small" @click="switchRole('lecturer')">Switch to Lecturer</el-button>
        <el-button size="small" @click="switchRole('student')">Switch to Student</el-button>
        <el-button size="small" @click="switchRole('advisor')">Switch to Advisor</el-button>
        <el-button size="small" @click="switchRole('admin')">Switch to Admin</el-button>
      </div>
      <el-button size="small" type="danger" @click="clearStorage">Clear Storage</el-button>
    </el-card>
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
            <div class="feature-item" @click="navigateTo('/student-courses')">
              <el-icon size="30"><Document /></el-icon>
              <h4>My Courses</h4>
              <p>View enrolled courses and marks</p>
            </div>
            <div class="feature-item" @click="navigateTo('/cgpa-calculator')">
              <el-icon size="30"><Calculator /></el-icon>
              <h4>CGPA Calculator</h4>
              <p>Calculate and track your CGPA</p>
            </div>
            <div class="feature-item" @click="navigateTo('/compare-performance')">
              <el-icon size="30"><TrendCharts /></el-icon>
              <h4>Compare Performance</h4>
              <p>Compare with classmates</p>
            </div>
          </div>
        </el-card>
      </template>

      <!-- Advisor Dashboard -->
      <template v-if="userRole === 'advisor'">
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
            <div class="feature-item" @click="navigateTo('/advisor-dashboard')">
              <el-icon size="30"><User /></el-icon>
              <h4>Manage Advisees</h4>
              <p>Monitor student progress</p>
            </div>
            <div class="feature-item" @click="navigateTo('/advisor-reports')">
              <el-icon size="30"><Document /></el-icon>
              <h4>Generate Reports</h4>
              <p>Create consultation reports</p>
            </div>
            <div class="feature-item" @click="navigateTo('/at-risk-students')">
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
            <div class="feature-item" @click="navigateTo('/user-management')">
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
  Monitor 
} from '@element-plus/icons-vue'

const router = useRouter()

// User data (get from localStorage/store)
const userRole = ref(localStorage.getItem('role') || localStorage.getItem('userRole') || 'student')
const userName = ref(localStorage.getItem('name') || localStorage.getItem('userName') || 'User')

// Debug: Check what's in localStorage
console.log('Dashboard Debug - Role from localStorage:', localStorage.getItem('role'))
console.log('Dashboard Debug - All localStorage items:', Object.keys(localStorage).map(key => ({
  key: key,
  value: localStorage.getItem(key)
})))

// Stats data
const stats = ref({})
const recentActivities = ref([])
const showDebug = ref(process.env.NODE_ENV === 'development') // Show debug panel in development

// Computed properties
const welcomeMessage = computed(() => {
  const messages = {
    lecturer: `Welcome back, ${userName.value}!`,
    student: `Hello, ${userName.value}!`,
    advisor: `Good day, ${userName.value}!`,
    admin: `Welcome, Administrator ${userName.value}!`
  }
  return messages[userRole.value] || 'Welcome!'
})

const roleDescription = computed(() => {
  const descriptions = {
    lecturer: 'Manage your courses, assessments, and track student performance.',
    student: 'View your academic progress, compare performance, and track your CGPA.',
    advisor: 'Monitor your advisees\' progress and provide academic guidance.',
    admin: 'Manage system users, courses, and monitor overall system health.'
  }
  return descriptions[userRole.value] || 'Welcome to the Course Marking System'
})

const roleIcon = computed(() => {
  const icons = {
    lecturer: '👨‍🏫',
    student: '🎓',
    advisor: '🛡️',
    admin: '⚙️'
  }
  return icons[userRole.value] || '👤'
})

const roleClass = computed(() => {
  return `welcome-${userRole.value}`
})

const roleTagType = computed(() => {
  const types = {
    lecturer: 'primary',
    student: 'success',
    advisor: 'warning',
    admin: 'danger'
  }
  return types[userRole.value] || 'info'
})

const quickActions = computed(() => {
  const actions = {
    lecturer: [
      { id: 1, label: 'Add Assessment', type: 'primary', icon: 'Plus', route: '/ca-components' },
      { id: 2, label: 'View Results', type: 'success', icon: 'View', route: '/lecturer-results' },
      { id: 3, label: 'Export Data', type: 'info', icon: 'Download', route: '/export' }
    ],
    student: [
      { id: 1, label: 'View Grades', type: 'primary', icon: 'View', route: '/student-courses' },
      { id: 2, label: 'Calculate CGPA', type: 'success', icon: 'Calculator', route: '/cgpa-calculator' },
      { id: 3, label: 'Compare Performance', type: 'warning', icon: 'TrendCharts', route: '/compare-performance' }
    ],
    advisor: [
      { id: 1, label: 'View Advisees', type: 'primary', icon: 'User', route: '/advisor-dashboard' },
      { id: 2, label: 'Add Meeting Note', type: 'success', icon: 'EditPen', route: '/advisor-dashboard' },
      { id: 3, label: 'Generate Report', type: 'info', icon: 'Document', route: '/advisor-reports' }
    ],
    admin: [
      { id: 1, label: 'Manage Users', type: 'primary', icon: 'User', route: '/user-management' },
      { id: 2, label: 'System Logs', type: 'warning', icon: 'Monitor', route: '/system-logs' },
      { id: 3, label: 'Backup Data', type: 'danger', icon: 'Download', route: '/backup' }
    ]
  }
  return actions[userRole.value] || []
})

// Methods
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

// 🐛 Debug method - remove in production
const switchRole = (newRole) => {
  localStorage.setItem('role', newRole)
  userRole.value = newRole
  fetchUserStats()
  ElMessage.success(`Switched to ${newRole} role`)
}

const clearStorage = () => {
  localStorage.clear()
  ElMessage.warning('Storage cleared - please login again')
  router.push('/login')
}

const fetchUserStats = async () => {
  try {
    // Get real data from localStorage (set during login)
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    
    const roleStats = {
      lecturer: {
        courses: user.courses_count || 0,
        students: user.students_count || 0,
        assessments: user.assessments_count || 0
      },
      student: {
        enrolledCourses: user.enrolled_courses || 0,
        cgpa: user.cgpa || '0.00',
        rank: user.rank || 'N/A'
      },
      advisor: {
        advisees: user.advisees_count || 0,
        atRiskStudents: user.at_risk_count || 0,
        meetings: user.meetings_count || 0
      },
      admin: {
        totalUsers: user.total_users || 0,
        totalCourses: user.total_courses || 0,
        systemHealth: user.system_health || 'Good'
      }
    }
    
    stats.value = roleStats[userRole.value] || {}
    
    console.log('📊 Dashboard stats loaded:', stats.value)
  } catch (error) {
    console.error('Error fetching stats:', error)
    stats.value = {}
  }
}

const fetchRecentActivities = async () => {
  try {
    // Mock data - replace with actual API calls
    const mockActivities = [
      {
        id: 1,
        description: 'Updated assessment scores for CS101',
        timestamp: '2025-06-25 10:30',
        type: 'primary'
      },
      {
        id: 2,
        description: 'New student enrolled in course',
        timestamp: '2025-06-25 09:15',
        type: 'success'
      },
      {
        id: 3,
        description: 'Generated monthly report',
        timestamp: '2025-06-24 16:45',
        type: 'info'
      }
    ]
    
    recentActivities.value = mockActivities
  } catch (error) {
    console.error('Error fetching activities:', error)
  }
}

onMounted(() => {
  // Force refresh role detection
  const storedRole = localStorage.getItem('role') || localStorage.getItem('userRole')
  const storedName = localStorage.getItem('name') || localStorage.getItem('userName')
  
  if (storedRole) {
    userRole.value = storedRole
    console.log('🔄 Dashboard mounted - Role detected:', storedRole)
  }
  
  if (storedName) {
    userName.value = storedName
    console.log('👤 Dashboard mounted - User:', storedName)
  }
  
  fetchUserStats()
  fetchRecentActivities()
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
}

.welcome-lecturer {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.welcome-student {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.welcome-advisor {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.welcome-admin {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

.welcome-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
}

.welcome-text {
  flex: 1;
}

.welcome-title {
  font-size: 28px;
  font-weight: bold;
  margin: 0 0 8px 0;
}

.welcome-description {
  font-size: 16px;
  margin: 0 0 16px 0;
  opacity: 0.9;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-name {
  font-weight: 600;
  font-size: 18px;
}

.welcome-icon {
  font-size: 64px;
  opacity: 0.8;
}

.quick-actions-card {
  margin-bottom: 24px;
  border-radius: 12px;
}

.section-title {
  margin: 0 0 16px 0;
  color: #333;
  font-size: 20px;
  font-weight: 600;
}

.actions-grid {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.action-button {
  flex: 1;
  min-width: 160px;
}

.dashboard-content {
  margin-bottom: 24px;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
}

.stat-icon {
  font-size: 32px;
  background: #f0f2f5;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-info h4 {
  margin: 0 0 4px 0;
  color: #666;
  font-size: 14px;
  font-weight: 500;
}

.stat-number {
  margin: 0;
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.text-warning {
  color: #e6a23c;
}

.text-success {
  color: #67c23a;
}

.feature-card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.feature-card h3 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 20px;
  font-weight: 600;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.feature-item {
  padding: 20px;
  border: 2px solid #f0f2f5;
  border-radius: 12px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
}

.feature-item:hover {
  border-color: #409eff;
  box-shadow: 0 4px 16px rgba(64, 158, 255, 0.1);
  transform: translateY(-2px);
}

.feature-item h4 {
  margin: 12px 0 8px 0;
  color: #333;
  font-size: 16px;
  font-weight: 600;
}

.feature-item p {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.activity-card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.debug-card {
  margin-top: 24px;
  border: 2px solid #f56c6c;
  border-radius: 12px;
  background: #fef0f0;
}

.debug-card h3 {
  color: #f56c6c;
  margin: 0 0 16px 0;
}

.debug-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 16px;
  }
  
  .welcome-content {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  
  .welcome-icon {
    font-size: 48px;
  }
  
  .actions-grid {
    flex-direction: column;
  }
  
  .action-button {
    width: 100%;
  }
  
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
}
</style>