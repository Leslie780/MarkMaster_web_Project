<template>
  <div class="dashboard-container">
    <!-- 背景装饰 -->
    <div class="bg-decoration">
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
      </div>
    </div>

    <!-- 主标题区域 -->
    <header class="hero-header">
      <div class="header-content">
        <div class="title-section">
          <div class="icon-wrapper">
            <div class="dashboard-icon">📊</div>
          </div>
          <h1 class="main-title">
            <span class="gradient-text">Lecturer Analytics</span>
            <div class="title-underline"></div>
          </h1>
          <p class="main-subtitle">
            Advanced Student Performance Dashboard with Real-time Insights
          </p>
        </div>

        <!-- 统计卡片 -->
        <div class="stats-grid">
          <div class="stat-card primary">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
              <div class="stat-number">{{ totalStudents }}</div>
              <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-trend">+12%</div>
          </div>

          <div class="stat-card success">
            <div class="stat-icon">📝</div>
            <div class="stat-info">
              <div class="stat-number">{{ studentsWithScores }}</div>
              <div class="stat-label">Graded Entries</div>
            </div>
            <div class="stat-trend">+8%</div>
          </div>

          <div class="stat-card warning">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
              <div class="stat-number">{{ averageScore }}%</div>
              <div class="stat-label">Average Score</div>
            </div>
            <div class="stat-trend">+3%</div>
          </div>

          <div class="stat-card info">
            <div class="stat-icon">🎯</div>
            <div class="stat-info">
              <div class="stat-number">{{ passRate }}%</div>
              <div class="stat-label">Pass Rate</div>
            </div>
            <div class="stat-trend">+5%</div>
          </div>
        </div>
      </div>
    </header>

    <!-- 控制面板 -->
    <section class="control-panel">
      <div class="panel-content">
        <div class="filter-section">
          <div class="filter-group">
            <label class="filter-label">Course Filter</label>
            <el-select
              v-model="selectedCourse"
              placeholder="Select Course"
              class="course-select"
              size="large"
            >
              <el-option label="All Courses" value="All">
                <span class="option-icon">📚</span>
                <span>All Courses</span>
              </el-option>
              <el-option
                v-for="course in courseOptions"
                :key="course"
                :label="course"
                :value="course"
              >
                <span class="option-icon">📖</span>
                <span>{{ course }}</span>
              </el-option>
            </el-select>
          </div>
        </div>

        <div class="action-buttons">
          <el-button
            type="primary"
            size="large"
            @click="exportCSV"
            class="export-btn"
          >
            <span class="btn-icon">📊</span>
            Export Data
          </el-button>
          <el-button size="large" @click="refreshData" class="refresh-btn">
            <span class="btn-icon">🔄</span>
            Refresh
          </el-button>
        </div>
      </div>
    </section>

    <!-- 图表区域 -->
    <section class="chart-section" v-if="filteredMarks.length">
      <div class="chart-header">
        <h3 class="chart-title">Performance Analytics</h3>
        <div class="chart-tabs">
          <button
            :class="['tab-btn', { active: chartType === 'bar' }]"
            @click="chartType = 'bar'"
          >
            📊 Bar Chart
          </button>
          <button
            :class="['tab-btn', { active: chartType === 'line' }]"
            @click="chartType = 'line'"
          >
            📈 Line Chart
          </button>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="resultsChart"></canvas>
      </div>
    </section>

    <!-- 学生卡片网格 -->
    <section class="students-section">
      <div class="section-header">
        <h3 class="section-title">Student Performance Details</h3>
        <div class="view-toggle">
          <button
            :class="['toggle-btn', { active: viewMode === 'grid' }]"
            @click="viewMode = 'grid'"
          >
            ⊞ Grid
          </button>
          <button
            :class="['toggle-btn', { active: viewMode === 'list' }]"
            @click="viewMode = 'list'"
          >
            ☰ List
          </button>
        </div>
      </div>

      <transition-group
        name="card-animation"
        tag="div"
        :class="['students-grid', viewMode]"
      >
        <div v-if="filteredMarks.length === 0" key="empty" class="empty-state">
          <div class="empty-icon">📭</div>
          <h4>No Data Available</h4>
          <p>No student records found for the selected course.</p>
        </div>

        <div
          v-for="(mark, index) in filteredMarks"
          :key="mark.student_name + mark.course_name"
          class="student-card"
          :style="{ animationDelay: `${index * 0.1}s` }"
        >
          <div class="card-header">
            <div class="student-avatar">
              {{ getInitials(mark.student_name) }}
            </div>
            <div class="student-info">
              <h4 class="student-name">{{ mark.student_name }}</h4>
              <span class="course-badge">{{ mark.course_name }}</span>
            </div>
            <div class="grade-indicator" :class="getGradeClass(mark.grade)">
              {{ mark.grade }}
            </div>
          </div>

          <div class="card-body">
            <div class="scores-grid">
              <div class="score-item">
                <span class="score-label">Coursework</span>
                <span class="score-value">{{ mark.ca_total }}</span>
              </div>
              <div class="score-item">
                <span class="score-label">Final Exam</span>
                <span class="score-value">{{ mark.final_exam }}</span>
              </div>
            </div>

            <div class="progress-section">
              <div class="progress-header">
                <span class="progress-label">Overall Score</span>
                <span class="progress-value">{{ mark.total }}%</span>
              </div>
              <div class="progress-bar-container">
                <div
                  class="progress-bar"
                  :style="{
                    width: `${mark.total}%`,
                    backgroundColor: getProgressColor(mark.total),
                  }"
                ></div>
              </div>
            </div>

            <div
              class="achievement-badge"
              :class="getAchievementClass(mark.achievement)"
            >
              <span class="achievement-icon">🏆</span>
              {{ mark.achievement }}
            </div>
          </div>
        </div>
      </transition-group>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import axios from "axios";
import Chart from "chart.js/auto";
import { ElMessage } from "element-plus";

const user = JSON.parse(localStorage.getItem("user") || "{}");
const studentMarks = ref([]);
const selectedCourse = ref("All");
const chartType = ref("bar");
const viewMode = ref("grid");
let chartInstance = null;

// 计算属性
const courseOptions = computed(() => {
  const courses = new Set(studentMarks.value.map((m) => m.course_name));
  return Array.from(courses);
});

const filteredMarks = computed(() => {
  if (selectedCourse.value === "All") return studentMarks.value;
  return studentMarks.value.filter(
    (m) => m.course_name === selectedCourse.value
  );
});

const totalStudents = computed(() => studentMarks.value.length);
const studentsWithScores = computed(
  () => studentMarks.value.filter((m) => m.total != null).length
);

const averageScore = computed(() => {
  const validScores = studentMarks.value.filter((m) => m.total != null);
  if (validScores.length === 0) return 0;
  const sum = validScores.reduce((acc, m) => acc + m.total, 0);
  return Math.round(sum / validScores.length);
});

const passRate = computed(() => {
  const validScores = studentMarks.value.filter((m) => m.total != null);
  if (validScores.length === 0) return 0;
  const passed = validScores.filter((m) => m.total >= 60).length;
  return Math.round((passed / validScores.length) * 100);
});

// 工具函数
function getInitials(name) {
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase();
}

function getProgressColor(score) {
  if (score >= 85) return "linear-gradient(135deg, #667eea 0%, #764ba2 100%)";
  if (score >= 70) return "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)";
  if (score >= 60) return "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)";
  return "linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)";
}

function getGradeClass(grade) {
  const gradeMap = {
    "A+": "grade-a-plus",
    A: "grade-a",
    "A-": "grade-a-minus",
    "B+": "grade-b-plus",
    B: "grade-b",
    "B-": "grade-b-minus",
    "C+": "grade-c-plus",
    C: "grade-c",
    D: "grade-d",
    F: "grade-f",
  };
  return gradeMap[grade] || "grade-default";
}

function getAchievementClass(achievement) {
  const achievementMap = {
    Excellent: "achievement-excellent",
    Good: "achievement-good",
    Satisfactory: "achievement-satisfactory",
    "Needs Improvement": "achievement-needs-improvement",
  };
  return achievementMap[achievement] || "achievement-default";
}

// Chart.js 渲染函数
async function drawChart() {
  await nextTick();
  const canvas = document.getElementById("resultsChart");
  if (!canvas) return;

  const ctx = canvas.getContext("2d");
  if (!ctx) return;

  if (chartInstance) chartInstance.destroy();

  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, "rgba(102, 126, 234, 0.8)");
  gradient.addColorStop(1, "rgba(118, 75, 162, 0.1)");

  chartInstance = new Chart(ctx, {
    type: chartType.value,
    data: {
      labels: filteredMarks.value.map((m) => m.student_name),
      datasets: [
        {
          label: "Total Score (%)",
          data: filteredMarks.value.map((m) => m.total),
          backgroundColor:
            chartType.value === "line" ? gradient : "rgba(102, 126, 234, 0.8)",
          borderColor: "#667eea",
          borderWidth: 2,
          borderRadius: chartType.value === "bar" ? 8 : 0,
          fill: chartType.value === "line",
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: `Student Performance Overview - ${selectedCourse.value}`,
          font: { size: 18, weight: "bold" },
          color: "#2c3e50",
        },
        legend: { display: false },
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          grid: {
            color: "rgba(0,0,0,0.05)",
          },
          ticks: {
            color: "#666",
          },
        },
        x: {
          grid: {
            display: false,
          },
          ticks: {
            color: "#666",
          },
        },
      },
      elements: {
        point: {
          radius: chartType.value === "line" ? 6 : 0,
          hoverRadius: 8,
        },
      },
    },
  });
}

// 导出 CSV
function exportCSV() {
  if (!filteredMarks.value.length) {
    ElMessage.warning("No data to export");
    return;
  }

  const headers = [
    "Student Name",
    "Course",
    "CA Total",
    "Final Exam",
    "Total",
    "Grade",
    "Achievement",
  ];
  const rows = filteredMarks.value.map((m) => [
    m.student_name,
    m.course_name,
    m.ca_total,
    m.final_exam,
    m.total,
    m.grade,
    m.achievement,
  ]);
  const csv = [headers, ...rows]
    .map((r) => r.map((v) => `"${v}"`).join(","))
    .join("\n");

  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = `lecturer_results_${
    new Date().toISOString().split("T")[0]
  }.csv`;
  link.click();

  ElMessage.success("Data exported successfully!");
}

// 刷新数据
async function refreshData() {
  await fetchStudentMarks();
  ElMessage.success("Data refreshed!");
}

// 获取成绩数据
async function fetchStudentMarks() {
  try {
    const res = await axios.get(
      `http://localhost:8085/lecturer-results?lecturer_id=${user.id}`
    );
    if (res.data.success) {
      studentMarks.value = res.data.data;
    } else {
      ElMessage.warning("No marks found.");
    }
  } catch (err) {
    ElMessage.error("Failed to load student marks.");
  }
}

// 初始化
onMounted(async () => {
  await fetchStudentMarks();
  drawChart();
});

// 监听变化
watch([filteredMarks, chartType], () => {
  drawChart();
});
</script>
<style scoped>
.dashboard-container {
  position: relative;
  padding: 2rem;
  font-family: "Segoe UI", Roboto, sans-serif;
  background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
  min-height: 100vh;
  overflow-x: hidden;
}

/* 背景装饰 */
.bg-decoration .floating-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 0;
  display: flex;
  justify-content: space-around;
  opacity: 0.08;
}
.shape {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}
.shape-1 {
  background: #667eea;
}
.shape-2 {
  background: #f093fb;
  animation-delay: 1s;
}
.shape-3 {
  background: #4facfe;
  animation-delay: 2s;
}
.shape-4 {
  background: #ff9a9e;
  animation-delay: 3s;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}

/* 主标题区域 */
.hero-header {
  position: relative;
  z-index: 1;
  text-align: center;
  margin-bottom: 2rem;
}
.icon-wrapper {
  font-size: 2.5rem;
}
.main-title {
  font-size: 2.2rem;
  margin: 0.5rem 0;
}
.gradient-text {
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
.title-underline {
  width: 60px;
  height: 4px;
  background: #667eea;
  margin: 0.5rem auto;
  border-radius: 2px;
}
.main-subtitle {
  font-size: 1rem;
  color: #666;
}

/* 统计卡片 */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  margin-top: 1.5rem;
}
.stat-card {
  background: white;
  border-radius: 1rem;
  padding: 1rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.3s;
}
.stat-card:hover {
  transform: translateY(-5px);
}
.stat-icon {
  font-size: 2rem;
}
.stat-number {
  font-size: 1.5rem;
  font-weight: bold;
}
.stat-label {
  color: #888;
}
.stat-trend {
  font-size: 0.9rem;
  color: #43a047;
  margin-top: 0.5rem;
}
.primary {
  border-left: 5px solid #667eea;
}
.success {
  border-left: 5px solid #43a047;
}
.warning {
  border-left: 5px solid #fbc02d;
}
.info {
  border-left: 5px solid #29b6f6;
}

/* 控制面板 */
.control-panel {
  margin-top: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}
.panel-content {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 1rem;
}
.filter-group {
  display: flex;
  flex-direction: column;
}
.filter-label {
  font-weight: 600;
  margin-bottom: 0.5rem;
}
.course-select {
  min-width: 200px;
}
.action-buttons {
  display: flex;
  gap: 0.5rem;
}
.export-btn,
.refresh-btn {
  display: flex;
  align-items: center;
}
.btn-icon {
  margin-right: 0.3rem;
}

/* 图表区域 */
.chart-section {
  margin-top: 2rem;
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.chart-title {
  font-size: 1.25rem;
}
.chart-tabs {
  display: flex;
  gap: 0.5rem;
}
.tab-btn {
  background: #eee;
  border: none;
  border-radius: 8px;
  padding: 0.4rem 0.8rem;
  cursor: pointer;
  transition: all 0.3s;
}
.tab-btn.active {
  background: #667eea;
  color: white;
}
.chart-container {
  height: 300px;
  margin-top: 1rem;
}

/* 学生卡片 */
.students-section {
  margin-top: 2rem;
}
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.students-grid.grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}
.students-grid.list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.student-card {
  background: white;
  border-radius: 1rem;
  padding: 1rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  animation: fadeInUp 0.4s ease forwards;
  opacity: 0;
}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.student-avatar {
  background: #667eea;
  color: white;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}
.student-info {
  flex-grow: 1;
  padding-left: 0.8rem;
}
.course-badge {
  font-size: 0.8rem;
  background: #eee;
  padding: 2px 6px;
  border-radius: 4px;
}
.grade-indicator {
  font-weight: bold;
}
.card-body {
  margin-top: 1rem;
}
.scores-grid {
  display: flex;
  justify-content: space-between;
}
.score-item {
  display: flex;
  flex-direction: column;
}
.score-label {
  font-size: 0.8rem;
  color: #777;
}
.score-value {
  font-weight: bold;
}
.progress-section {
  margin-top: 1rem;
}
.progress-bar-container {
  background: #eee;
  border-radius: 10px;
  overflow: hidden;
  height: 10px;
}
.progress-bar {
  height: 100%;
  transition: width 0.6s ease;
}
.achievement-badge {
  margin-top: 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.6rem;
  border-radius: 0.5rem;
  background: #f0f0f0;
}
.achievement-excellent {
  background: #c3f584;
  color: #33691e;
}
.achievement-good {
  background: #b2ebf2;
  color: #006064;
}
.achievement-satisfactory {
  background: #ffe082;
  color: #ef6c00;
}
.achievement-needs-improvement {
  background: #ffab91;
  color: #bf360c;
}
.empty-state {
  text-align: center;
  padding: 2rem;
  color: #888;
}
.empty-icon {
  font-size: 3rem;
}
.view-toggle .toggle-btn {
  border: none;
  background: #eee;
  padding: 0.4rem 0.8rem;
  border-radius: 0.5rem;
  cursor: pointer;
}
.view-toggle .toggle-btn.active {
  background: #667eea;
  color: white;
}
</style>
