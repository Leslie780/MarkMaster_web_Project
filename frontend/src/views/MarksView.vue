<template>
  <div class="dashboard-container">
    <div class="bg-decoration">
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
      </div>
    </div>

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

          <div class="filter-group">
            <label class="filter-label">Sort By</label>
            <el-select
              v-model="sortBy"
              placeholder="Sort By"
              class="sort-select"
              size="large"
            >
              <el-option label="Name (A-Z)" value="name_asc">
                <span class="option-icon">🔤</span>
                <span>Name (A-Z)</span>
              </el-option>
              <el-option label="Name (Z-A)" value="name_desc">
                <span class="option-icon">🔤</span>
                <span>Name (Z-A)</span>
              </el-option>
              <el-option label="Score (High to Low)" value="score_desc">
                <span class="option-icon">📈</span>
                <span>Score (High to Low)</span>
              </el-option>
              <el-option label="Score (Low to High)" value="score_asc">
                <span class="option-icon">📉</span>
                <span>Score (Low to High)</span>
              </el-option>
              <el-option label="Grade (A to F)" value="grade_asc">
                <span class="option-icon">🏆</span>
                <span>Grade (A to F)</span>
              </el-option>
              <el-option label="Grade (F to A)" value="grade_desc">
                <span class="option-icon">🏆</span>
                <span>Grade (F to A)</span>
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
        </div>
      </div>
    </section>

    <!-- 图表区域 -->
    <section class="chart-section" v-if="sortedMarks.length">
      <div class="chart-header">
        <h3 class="chart-title">Performance Analytics</h3>
        <div class="chart-controls">
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
          <div class="ranking-toggle">
            <el-switch
              v-model="showRanking"
              active-text="Show Rankings"
              inactive-text="Hide Rankings"
              size="large"
            />
          </div>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="resultsChart"></canvas>
      </div>

      <!-- Rankings Table -->
      <div v-if="showRanking" class="rankings-section">
        <h4 class="rankings-title">🏆 Performance Rankings</h4>
        <div class="rankings-table">
          <div class="ranking-header">
            <span class="rank-col">Rank</span>
            <span class="name-col">Student Name</span>
            <span class="score-col">Total Score</span>
            <span class="grade-col">Grade</span>
            <span class="percentile-col">Percentile</span>
          </div>
          <div
            v-for="(student, index) in topPerformers"
            :key="student.student_name"
            class="ranking-row"
            :class="getRankingClass(index + 1)"
          >
            <span class="rank-col">
              <div class="rank-badge">
                {{ getRankIcon(index + 1) }} {{ index + 1 }}
              </div>
            </span>
            <span class="name-col">{{ student.student_name }}</span>
            <span class="score-col">{{ student.total }}%</span>
            <span class="grade-col">
              <span :class="['grade-badge', getGradeClass(student.grade)]">
                {{ student.grade }}
              </span>
            </span>
            <span class="percentile-col"
              >{{ getPercentile(student.total) }}th</span
            >
          </div>
        </div>
      </div>
    </section>

    <!-- 学生卡片网格 -->
    <section class="students-section">
      <div class="section-header">
        <h3 class="section-title">Student Performance Details</h3>
        <div class="section-controls">
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

          <div class="pagination-info">
            <span class="info-text">
              Showing {{ (currentPage - 1) * pageSize + 1 }}-{{
                Math.min(currentPage * pageSize, sortedMarks.length)
              }}
              of {{ sortedMarks.length }} students
            </span>
          </div>
        </div>
      </div>

      <transition-group
        name="card-animation"
        tag="div"
        :class="['students-grid', viewMode]"
      >
        <div v-if="paginatedMarks.length === 0" key="empty" class="empty-state">
          <div class="empty-icon">📭</div>
          <h4>No Data Available</h4>
          <p>No student records found for the selected course.</p>
        </div>

        <div
          v-for="(mark, index) in paginatedMarks"
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
            <div class="card-ranking">
              <div class="overall-rank">#{{ getStudentRank(mark) }}</div>
              <div class="grade-indicator" :class="getGradeClass(mark.grade)">
                {{ mark.grade }}
              </div>
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

            <div class="performance-metrics">
              <div class="metric-item">
                <span class="metric-label">Percentile</span>
                <span class="metric-value"
                  >{{ getPercentile(mark.total) }}th</span
                >
              </div>
              <div class="metric-item">
                <span class="metric-label">Class Avg</span>
                <span class="metric-value">{{ averageScore }}%</span>
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

      <!-- 分页控件 -->
      <div v-if="sortedMarks.length > pageSize" class="pagination-section">
        <el-pagination
          v-model:current-page="currentPage"
          :page-size="pageSize"
          :total="sortedMarks.length"
          :page-sizes="[10, 20, 50, 100]"
          :small="false"
          :disabled="false"
          :background="true"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
          class="custom-pagination"
        />
      </div>
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
const sortBy = ref("score_desc");
const chartType = ref("bar");
const viewMode = ref("grid");
const showRanking = ref(true);
const currentPage = ref(1);
const pageSize = ref(20);
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

const sortedMarks = computed(() => {
  let marks = [...filteredMarks.value];

  switch (sortBy.value) {
    case "name_asc":
      marks.sort((a, b) => a.student_name.localeCompare(b.student_name));
      break;
    case "name_desc":
      marks.sort((a, b) => b.student_name.localeCompare(a.student_name));
      break;
    case "score_desc":
      marks.sort((a, b) => (b.total || 0) - (a.total || 0));
      break;
    case "score_asc":
      marks.sort((a, b) => (a.total || 0) - (b.total || 0));
      break;
    case "grade_asc":
      marks.sort((a, b) => getGradeValue(a.grade) - getGradeValue(b.grade));
      break;
    case "grade_desc":
      marks.sort((a, b) => getGradeValue(b.grade) - getGradeValue(a.grade));
      break;
    default:
      marks.sort((a, b) => (b.total || 0) - (a.total || 0));
  }

  return marks;
});

const paginatedMarks = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return sortedMarks.value.slice(start, end);
});

const topPerformers = computed(() => {
  return [...sortedMarks.value]
    .sort((a, b) => (b.total || 0) - (a.total || 0))
    .slice(0, 10);
});

const totalStudents = computed(() => filteredMarks.value.length);

const studentsWithScores = computed(
  () => filteredMarks.value.filter((m) => m.total != null).length
);

const averageScore = computed(() => {
  const validScores = filteredMarks.value.filter((m) => m.total != null);
  if (validScores.length === 0) return 0;
  const sum = validScores.reduce((acc, m) => acc + m.total, 0);
  return Math.round(sum / validScores.length);
});

const passRate = computed(() => {
  const validScores = filteredMarks.value.filter((m) => m.total != null);
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

function getGradeValue(grade) {
  const gradeValues = {
    "A+": 10,
    A: 9,
    "A-": 8,
    "B+": 7,
    B: 6,
    "B-": 5,
    "C+": 4,
    C: 3,
    D: 2,
    F: 1,
  };
  return gradeValues[grade] || 0;
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

function getStudentRank(student) {
  const sortedByScore = [...sortedMarks.value].sort(
    (a, b) => (b.total || 0) - (a.total || 0)
  );
  return (
    sortedByScore.findIndex(
      (s) =>
        s.student_name === student.student_name &&
        s.course_name === student.course_name
    ) + 1
  );
}

function getRankIcon(rank) {
  if (rank === 1) return "🥇";
  if (rank === 2) return "🥈";
  if (rank === 3) return "🥉";
  return "🏅";
}

function getRankingClass(rank) {
  if (rank <= 3) return "top-three";
  if (rank <= 10) return "top-ten";
  return "";
}

function getPercentile(score) {
  const validScores = sortedMarks.value
    .filter((m) => m.total != null)
    .map((m) => m.total)
    .sort((a, b) => a - b);

  if (validScores.length === 0) return 0;

  const belowScore = validScores.filter((s) => s < score).length;
  return Math.round((belowScore / validScores.length) * 100);
}

// 分页处理函数
function handleSizeChange(size) {
  pageSize.value = size;
  currentPage.value = 1;
}

function handleCurrentChange(page) {
  currentPage.value = page;
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

  const chartData = showRanking.value ? topPerformers.value : sortedMarks.value;

  chartInstance = new Chart(ctx, {
    type: chartType.value,
    data: {
      labels: chartData.map((m, index) =>
        showRanking.value ? `#${index + 1} ${m.student_name}` : m.student_name
      ),
      datasets: [
        {
          label: "Total Score (%)",
          data: chartData.map((m) => m.total),
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
          text: `${
            showRanking.value ? "Top Performers" : "Student Performance"
          } - ${selectedCourse.value}`,
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
            maxRotation: 45,
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
  if (!sortedMarks.value.length) {
    ElMessage.warning("No data to export");
    return;
  }

  const headers = [
    "Rank",
    "Student Name",
    "Course",
    "CA Total",
    "Final Exam",
    "Total",
    "Grade",
    "Achievement",
    "Percentile",
  ];
  const rows = sortedMarks.value.map((m) => [
    getStudentRank(m),
    m.student_name,
    m.course_name,
    m.ca_total,
    m.final_exam,
    m.total,
    m.grade,
    m.achievement,
    getPercentile(m.total),
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
watch([sortedMarks, chartType, showRanking], () => {
  drawChart();
});

watch([selectedCourse, sortBy], () => {
  currentPage.value = 1;
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
.bg-decoration {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 0;
}

.bg-decoration .floating-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 0;
  display: flex;
  justify-content: space-around;
  opacity: 0.08;
  height: 100vh;
  align-items: center;
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
    transform: translateY(0) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(5deg);
  }
}

/* 主标题区域 */
.hero-header {
  position: relative;
  z-index: 1;
  text-align: center;
  margin-bottom: 2rem;
}

.header-content {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.title-section {
  margin-bottom: 2rem;
}

.icon-wrapper {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.dashboard-icon {
  display: inline-block;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.main-title {
  font-size: 2.5rem;
  margin: 0.5rem 0;
  font-weight: 700;
}

.gradient-text {
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.title-underline {
  width: 80px;
  height: 4px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  margin: 0.5rem auto;
  border-radius: 2px;
  animation: expand 2s ease-in-out infinite alternate;
}

@keyframes expand {
  0% {
    width: 60px;
  }
  100% {
    width: 100px;
  }
}

.main-subtitle {
  font-size: 1.1rem;
  color: #666;
  margin-top: 1rem;
}

/* 统计卡片 */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.stat-card {
  background: white;
  border-radius: 1.2rem;
  padding: 1.5rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.stat-card:hover::before {
  transform: scaleX(1);
}

.stat-icon {
  font-size: 2.5rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 1rem;
  background: rgba(102, 126, 234, 0.1);
}

.stat-info {
  flex-grow: 1;
}

.stat-number {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 0.2rem;
}

.stat-label {
  color: #7f8c8d;
  font-size: 0.9rem;
  font-weight: 500;
}

.stat-trend {
  font-size: 0.85rem;
  font-weight: 600;
  color: #27ae60;
  background: rgba(39, 174, 96, 0.1);
  padding: 0.2rem 0.5rem;
  border-radius: 1rem;
  margin-top: 0.5rem;
}

.primary .stat-icon {
  background: rgba(102, 126, 234, 0.15);
  color: #667eea;
}

.success .stat-icon {
  background: rgba(67, 160, 71, 0.15);
  color: #43a047;
}

.warning .stat-icon {
  background: rgba(251, 192, 45, 0.15);
  color: #fbc02d;
}

.info .stat-icon {
  background: rgba(41, 182, 246, 0.15);
  color: #29b6f6;
}

/* 控制面板 */
.control-panel {
  position: relative;
  z-index: 1;
  margin-top: 2rem;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.panel-content {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1.5rem;
}

.filter-section {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  flex-grow: 1;
}

.filter-group {
  display: flex;
  flex-direction: column;
  min-width: 200px;
}

.filter-label {
  font-weight: 600;
  margin-bottom: 0.8rem;
  color: #2c3e50;
  font-size: 0.95rem;
}

.course-select,
.sort-select {
  min-width: 220px;
}

.option-icon {
  margin-right: 0.5rem;
}

.action-buttons {
  display: flex;
  gap: 0.8rem;
  flex-shrink: 0;
}

.export-btn,
.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  border-radius: 0.8rem;
  transition: all 0.3s ease;
}

.export-btn {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  color: white;
}

.export-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.refresh-btn {
  background: white;
  border: 2px solid #e0e0e0;
  color: #666;
}

.refresh-btn:hover {
  border-color: #667eea;
  color: #667eea;
}

.btn-icon {
  font-size: 1rem;
}

/* 图表区域 */
.chart-section {
  position: relative;
  z-index: 1;
  margin-top: 2rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.chart-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2c3e50;
}

.chart-controls {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.chart-tabs {
  display: flex;
  background: #f8f9fa;
  border-radius: 0.8rem;
  padding: 0.3rem;
  gap: 0.2rem;
}

.tab-btn {
  background: transparent;
  border: none;
  border-radius: 0.6rem;
  padding: 0.6rem 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  color: #666;
}

.tab-btn.active {
  background: #667eea;
  color: white;
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.tab-btn:hover:not(.active) {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.ranking-toggle .el-switch {
  --el-switch-on-color: #667eea;
}

.chart-container {
  height: 400px;
  margin-top: 1.5rem;
  position: relative;
}

/* Rankings Table */
.rankings-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f0f0f0;
}

.rankings-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 1rem;
}

.rankings-table {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.ranking-header {
  display: grid;
  grid-template-columns: 80px 1fr 120px 100px 100px;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
}

.ranking-row {
  display: grid;
  grid-template-columns: 80px 1fr 120px 100px 100px;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.3s ease;
  align-items: center;
}

.ranking-row:hover {
  background: rgba(102, 126, 234, 0.05);
}

.ranking-row.top-three {
  background: linear-gradient(
    135deg,
    rgba(255, 215, 0, 0.1),
    rgba(255, 215, 0, 0.05)
  );
}

.ranking-row.top-ten {
  background: rgba(102, 126, 234, 0.03);
}

.rank-col,
.name-col,
.score-col,
.grade-col,
.percentile-col {
  display: flex;
  align-items: center;
}

.rank-badge {
  background: #f8f9fa;
  padding: 0.3rem 0.6rem;
  border-radius: 1rem;
  font-weight: 600;
  color: #666;
  font-size: 0.85rem;
}

.top-three .rank-badge {
  background: linear-gradient(135deg, #ffd700, #ffed4e);
  color: #b8860b;
}

.grade-badge {
  padding: 0.2rem 0.6rem;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.8rem;
}

.grade-a-plus,
.grade-a {
  background: #d4edda;
  color: #155724;
}

.grade-a-minus,
.grade-b-plus {
  background: #d1ecf1;
  color: #0c5460;
}

.grade-b,
.grade-b-minus {
  background: #fff3cd;
  color: #856404;
}

.grade-c-plus,
.grade-c {
  background: #ffeaa7;
  color: #6c5ce7;
}

.grade-d,
.grade-f {
  background: #f8d7da;
  color: #721c24;
}

/* 学生卡片区域 */
.students-section {
  position: relative;
  z-index: 1;
  margin-top: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2c3e50;
}

.section-controls {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.view-toggle {
  display: flex;
  background: #f8f9fa;
  border-radius: 0.8rem;
  padding: 0.3rem;
  gap: 0.2rem;
}

.toggle-btn {
  border: none;
  background: transparent;
  padding: 0.5rem 1rem;
  border-radius: 0.6rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  color: #666;
}

.toggle-btn.active {
  background: #667eea;
  color: white;
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.toggle-btn:hover:not(.active) {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}

.pagination-info {
  font-size: 0.9rem;
  color: #666;
}

.info-text {
  font-weight: 500;
}

/* 学生卡片网格 */
.students-grid.grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
}

.students-grid.list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.students-grid.list .student-card {
  max-width: none;
}

.student-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 1.2rem;
  padding: 1.5rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  animation: fadeInUp 0.6s ease forwards;
  opacity: 0;
  position: relative;
  overflow: hidden;
}

.student-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.student-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.student-card:hover::before {
  transform: scaleX(1);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
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
  margin-bottom: 1.5rem;
}

.student-avatar {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.student-info {
  flex-grow: 1;
  padding-left: 1rem;
}

.student-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.3rem 0;
}

.course-badge {
  font-size: 0.8rem;
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  padding: 0.2rem 0.6rem;
  border-radius: 1rem;
  font-weight: 600;
}

.card-ranking {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.overall-rank {
  font-size: 0.9rem;
  font-weight: 600;
  color: #666;
  background: #f8f9fa;
  padding: 0.2rem 0.6rem;
  border-radius: 1rem;
}

.grade-indicator {
  font-weight: bold;
  font-size: 1.1rem;
  padding: 0.3rem 0.6rem;
  border-radius: 0.5rem;
  min-width: 40px;
  text-align: center;
}

.card-body {
  margin-top: 1rem;
}

.scores-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.score-item {
  background: #f8f9fa;
  padding: 0.8rem;
  border-radius: 0.8rem;
  text-align: center;
}

.score-label {
  font-size: 0.8rem;
  color: #666;
  font-weight: 600;
  display: block;
  margin-bottom: 0.3rem;
}

.score-value {
  font-weight: bold;
  font-size: 1.2rem;
  color: #2c3e50;
}

.progress-section {
  margin-bottom: 1.5rem;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.progress-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #666;
}

.progress-value {
  font-weight: bold;
  color: #2c3e50;
}

.progress-bar-container {
  background: #e9ecef;
  border-radius: 1rem;
  overflow: hidden;
  height: 12px;
  position: relative;
}

.progress-bar {
  height: 100%;
  border-radius: 1rem;
  transition: width 0.8s ease;
  position: relative;
}

.progress-bar::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.performance-metrics {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.metric-item {
  background: #f8f9fa;
  padding: 0.6rem;
  border-radius: 0.6rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.metric-label {
  font-size: 0.8rem;
  color: #666;
  font-weight: 600;
}

.metric-value {
  font-weight: bold;
  color: #2c3e50;
  font-size: 0.9rem;
}

.achievement-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-radius: 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
  width: 100%;
  justify-content: center;
}

.achievement-icon {
  font-size: 1rem;
}

.achievement-excellent {
  background: linear-gradient(135deg, #a8e6cf, #88d8a3);
  color: #2d5016;
}

.achievement-good {
  background: linear-gradient(135deg, #81ecec, #74b9ff);
  color: #0984e3;
}

.achievement-satisfactory {
  background: linear-gradient(135deg, #fdcb6e, #f39c12);
  color: #b8651b;
}

.achievement-needs-improvement {
  background: linear-gradient(135deg, #fd79a8, #e84393);
  color: #c0392b;
}

.achievement-default {
  background: #f8f9fa;
  color: #666;
}

/* 空状态 */
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #888;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 1rem;
  backdrop-filter: blur(10px);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h4 {
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
  color: #666;
}

.empty-state p {
  color: #888;
}

/* 分页控件 */
.pagination-section {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
}

.custom-pagination {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 1rem 2rem;
  border-radius: 1rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

/* 动画 */
.card-animation-enter-active,
.card-animation-leave-active {
  transition: all 0.5s ease;
}

.card-animation-enter-from {
  opacity: 0;
  transform: translateY(30px) scale(0.95);
}

.card-animation-leave-to {
  opacity: 0;
  transform: translateY(-30px) scale(0.95);
}

.card-animation-move {
  transition: transform 0.5s ease;
}

/* 响应式设计 */
@media (max-width: 1200px) {
  .dashboard-container {
    padding: 1.5rem;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  }

  .students-grid.grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }

  .main-title {
    font-size: 2rem;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
  }

  .stat-card {
    padding: 1rem;
    flex-direction: column;
    text-align: center;
  }

  .stat-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
  }

  .stat-number {
    font-size: 1.5rem;
  }

  .panel-content {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-section {
    flex-direction: column;
    gap: 1rem;
  }

  .chart-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .chart-controls {
    justify-content: space-between;
  }

  .chart-container {
    height: 300px;
  }

  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .section-controls {
    justify-content: space-between;
  }

  .students-grid.grid {
    grid-template-columns: 1fr;
  }

  .card-header {
    flex-wrap: wrap;
    gap: 1rem;
  }

  .ranking-header,
  .ranking-row {
    grid-template-columns: 60px 1fr 80px 70px 70px;
    gap: 0.5rem;
    padding: 0.8rem;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .main-title {
    font-size: 1.8rem;
  }

  .main-subtitle {
    font-size: 1rem;
  }

  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }

  .chart-container {
    height: 250px;
  }

  .student-card {
    padding: 1rem;
  }

  .scores-grid {
    grid-template-columns: 1fr;
  }

  .performance-metrics {
    grid-template-columns: 1fr;
  }

  .ranking-header,
  .ranking-row {
    grid-template-columns: 50px 1fr 60px;
    gap: 0.3rem;
  }

  .grade-col,
  .percentile-col {
    display: none;
  }
}

/* Element Plus 组件样式覆盖 */
.el-select {
  width: 100%;
}

.el-select .el-input {
  border-radius: 0.8rem;
}

.el-select .el-input__wrapper {
  border-radius: 0.8rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.el-select .el-input__wrapper:hover {
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
}

.el-select .el-input__wrapper.is-focus {
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.25);
}

.el-button {
  border-radius: 0.8rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.el-button--primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
}

.el-button--primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.el-pagination {
  justify-content: center;
}

.el-pagination .el-pager li {
  border-radius: 0.8rem;
  padding: 0.4rem 0.8rem;
  transition: all 0.3s ease;
}
.el-pagination .el-pager li:hover {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
}
.el-pagination .el-pager li.active {
  background: #667eea;
  color: white;
}
.el-pagination .el-pager li.active:hover {
  background: #5a6bbd;
  color: white;
}

.el-pagination .el-pager li.disabled {
  color: #ccc;
  cursor: not-allowed;
}
.el-pagination .el-pager li.disabled:hover {
  background: transparent;
  color: #ccc;
}
.el-pagination .el-pager li.disabled .el-icon {
  color: #ccc;
}
.el-pagination .el-pager li .el-icon {
  font-size: 1.2rem;
}
.el-pagination .el-pager li .el-icon-arrow-left,
.el-pagination .el-pager li .el-icon-arrow-right {
  font-size: 1.2rem;
}
</style>
