<template>
  <div class="dashboard-container">
    <el-card class="welcome-card">
      <h2 class="welcome-title">Welcome to the Course Marking System 🎓</h2>
      <p class="welcome-description">
        This system allows lecturers to manage course assessments, students to
        view their grades, and advisors/admins to monitor student performance.
      </p>
    </el-card>

    <div class="dashboard-grid">
      <el-card class="info-card">
        <h3>📘 For Lecturers</h3>
        <ul>
          <li>📌 Manage courses & students</li>
          <li>🧪 Add and evaluate assessments</li>
          <li>📊 Track final exam results</li>
        </ul>
      </el-card>

      <el-card class="info-card">
        <h3>🎯 For Students</h3>
        <ul>
          <li>📄 View enrolled courses</li>
          <li>📈 Check assessment scores</li>
          <li>🏆 Compare with classmates</li>
        </ul>
      </el-card>

      <el-card class="info-card">
        <h3>🛡️ For Admin/Advisor</h3>
        <ul>
          <li>🔧 Reset passwords</li>
          <li>👥 Manage users</li>
          <li>📋 Review academic performance</li>
        </ul>
      </el-card>
    </div>
  </div>
</template>

<script setup>
// no logic needed for static display
</script>

<style scoped>
.dashboard-container {
  padding: 32px;
  background: #f9f9f9;
}

.welcome-card {
  margin-bottom: 32px;
  padding: 24px;
  background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
  text-align: center;
  border-radius: 16px;
}

.welcome-title {
  font-size: 28px;
  font-weight: bold;
  color: #2c3e50;
}

.welcome-description {
  font-size: 16px;
  color: #555;
  margin-top: 12px;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.info-card {
  padding: 20px;
  border-radius: 12px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.info-card h3 {
  margin-bottom: 12px;
  font-size: 18px;
  color: #333;
}

.info-card ul {
  padding-left: 20px;
  color: #666;
}

.info-card li {
  margin-bottom: 6px;
}
</style>
