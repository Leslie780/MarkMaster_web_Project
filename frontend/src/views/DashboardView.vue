<template>
  <div class="dashboard-container">
    <el-card class="welcome-card">
      <template #header>
        <div class="card-header">
          <span class="card-title">Dashboard</span>
          <span class="card-time">{{ currentTime }}</span>
        </div>
      </template>

      <div class="dashboard-content">
        <h2 class="welcome-title">🎓 Welcome to MarkMaster</h2>

        <el-row :gutter="20">
          <el-col :span="6">
            <el-card class="stat-card" shadow="hover">
              <div class="stat-content">
                <div class="stat-icon" style="background-color: #d6a77a">
                  <el-icon size="24"><Document /></el-icon>
                </div>
                <div class="stat-info">
                  <div class="stat-number">15</div>
                  <div class="stat-label">Total Courses</div>
                </div>
              </div>
            </el-card>
          </el-col>

          <el-col :span="6">
            <el-card class="stat-card" shadow="hover">
              <div class="stat-content">
                <div class="stat-icon" style="background-color: #a8b0a2">
                  <el-icon size="24"><User /></el-icon>
                </div>
                <div class="stat-info">
                  <div class="stat-number">342</div>
                  <div class="stat-label">Total Students</div>
                </div>
              </div>
            </el-card>
          </el-col>

          <el-col :span="6">
            <el-card class="stat-card" shadow="hover">
              <div class="stat-content">
                <div class="stat-icon" style="background-color: #bfa18f">
                  <el-icon size="24"><UserFilled /></el-icon>
                </div>
                <div class="stat-info">
                  <div class="stat-number">28</div>
                  <div class="stat-label">Total Lecturers</div>
                </div>
              </div>
            </el-card>
          </el-col>

          <el-col :span="6">
            <el-card class="stat-card" shadow="hover">
              <div class="stat-content">
                <div class="stat-icon" style="background-color: #c6aa9a">
                  <el-icon size="24"><PieChart /></el-icon>
                </div>
                <div class="stat-info">
                  <div class="stat-number">12</div>
                  <div class="stat-label">Pending Reviews</div>
                </div>
              </div>
            </el-card>
          </el-col>
        </el-row>

        <div class="status-alert">
          <el-alert
            title="System Status"
            description="All systems are running normally. Last sync: 2 minutes ago."
            type="success"
            :closable="false"
            show-icon
          />
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Document, User, UserFilled, PieChart } from "@element-plus/icons-vue";

const currentTime = ref("");

const updateTime = () => {
  currentTime.value = new Date().toLocaleString();
};

let timer;

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timer) {
    clearInterval(timer);
  }
});
</script>

<style scoped>
.dashboard-container {
  height: 100%;
  padding: 0;
  background-color: #f9f6f1;
  overflow-y: auto;
}

.welcome-card {
  background-color: #fff9f2;
  border: 1px solid #e5ded7;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  border-radius: 12px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 18px;
  font-weight: bold;
  color: #4a4a4a;
}

.card-time {
  font-size: 14px;
  color: #888;
}

.dashboard-content {
  padding: 20px 0;
  color: #4a4a4a;
}

.welcome-title {
  color: #d6a77a;
  margin-bottom: 16px;
}

.route-path {
  color: #666;
  margin-bottom: 24px;
}

.stat-card {
  margin-bottom: 16px;
  background-color: #fff9f2 !important;
  border: 1px solid #e5ded7;
  border-radius: 12px;
}

.stat-content {
  display: flex;
  align-items: center;
  padding: 16px 0;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  color: white;
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  color: #4a4a4a;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #7a7a7a;
}

.status-alert {
  margin-top: 24px;
}

/* Alert override */
:deep(.el-alert) {
  background-color: #fff4e9;
  border-color: #f0e5da;
  color: #4a4a4a;
  border-radius: 8px;
}

/* Scrollbar warm gray style */
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-track {
  background: #f1ede7;
}
::-webkit-scrollbar-thumb {
  background-color: #c1b6a4;
  border-radius: 8px;
}

/* Code block style */
code {
  background-color: #f1f1f1;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: "Courier New", monospace;
  color: #4a4a4a;
}
</style>
