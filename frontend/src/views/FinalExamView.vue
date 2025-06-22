<template>
  <div class="assessment-management">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="page-title">
            <i class="el-icon-edit-outline title-icon"></i>
            Final Exam Scores
          </h1>
          <p class="page-subtitle">
            Manage and update student final exam results
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card">
            <div class="stat-number">{{ students.length }}</div>
            <div class="stat-label">Total Students</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ completedScores }}</div>
            <div class="stat-label">Scores Entered</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">
              {{ students.length - completedScores }}
            </div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
      </div>
    </div>

    <!-- 控制面板 -->
    <el-card class="control-panel">
      <div class="control-content">
        <div class="course-selector">
          <label class="selector-label">Course Selection</label>
          <el-select
            v-model="selectedCourseId"
            placeholder="🔍 Select a course to manage scores"
            class="course-select"
            size="large"
            @change="loadScores"
            clearable
            filterable
          >
            <el-option
              v-for="course in myCourses"
              :key="course.id"
              :label="`${course.course_name} (${course.course_code || 'N/A'})`"
              :value="course.id"
            >
              <div class="course-option">
                <span class="course-name">{{ course.course_name }}</span>
                <span class="course-code">{{
                  course.course_code || "N/A"
                }}</span>
              </div>
            </el-option>
          </el-select>
        </div>
        <div>
          <el-button
            type="primary"
            size="large"
            class="add-button"
            @click="submitScores"
            :loading="saving"
            :disabled="completedScores === 0 || !selectedCourseId"
          >
            <i class="el-icon-check" v-if="!saving"></i>
            {{ saving ? "Saving..." : "Save All Scores" }}
          </el-button>
        </div>
      </div>
    </el-card>

    <!-- Loading State -->
    <div v-if="loading" class="select-course-prompt">
      <el-loading-spinner class="prompt-icon" />
      <p>Loading student data...</p>
    </div>

    <!-- Students Table -->
    <div
      v-else-if="selectedCourseId && students.length > 0"
      class="components-section"
    >
      <el-card class="component-card">
        <div class="card-header">
          <div class="component-info">
            <h3 class="component-title">Student Scores Management</h3>
            <el-tag type="info" class="type-tag">Final Exam</el-tag>
          </div>
          <div class="component-actions">
            <el-button size="small" @click="clearAllScores" :disabled="saving">
              <i class="el-icon-refresh-left"></i>
              Clear All
            </el-button>
          </div>
        </div>

        <div class="card-content">
          <div class="component-details">
            <div class="detail-item">
              <i class="el-icon-user detail-icon"></i>
              <span class="detail-label">Total Students:</span>
              <span class="detail-value">{{ students.length }}</span>
            </div>
            <div class="detail-item">
              <i class="el-icon-check detail-icon"></i>
              <span class="detail-label">Completed:</span>
              <span class="detail-value">{{ completedScores }}</span>
            </div>
            <div class="detail-item">
              <i class="el-icon-time detail-icon"></i>
              <span class="detail-label">Progress:</span>
              <span class="detail-value"
                >{{
                  Math.round((completedScores / students.length) * 100)
                }}%</span
              >
            </div>
          </div>

          <!-- Scores Table -->
          <el-table
            :data="students"
            style="width: 100%"
            :stripe="true"
            :header-cell-style="{
              backgroundColor: '#f8f9fa',
              color: '#2c3e50',
              fontWeight: '600',
            }"
          >
            <el-table-column label="Matric No" prop="matric_no" width="150">
              <template #default="{ row }">
                <el-tag type="info" effect="plain" size="small">
                  {{ row.matric_no }}
                </el-tag>
              </template>
            </el-table-column>

            <el-table-column label="Student Name" prop="name" min-width="200">
              <template #default="{ row }">
                <div style="display: flex; align-items: center">
                  <div
                    style="
                      background: #409eff;
                      color: white;
                      border-radius: 50%;
                      width: 32px;
                      height: 32px;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      font-weight: bold;
                      margin-right: 12px;
                      font-size: 12px;
                    "
                  >
                    {{ getInitials(row.name) }}
                  </div>
                  <div>
                    <div style="font-weight: 600; color: #2c3e50">
                      {{ row.name }}
                    </div>
                    <div style="font-size: 12px; color: #7f8c8d">
                      {{ row.email || "No email" }}
                    </div>
                  </div>
                </div>
              </template>
            </el-table-column>

            <el-table-column
              label="Final Exam Score"
              width="200"
              align="center"
            >
              <template #default="{ row }">
                <div
                  style="
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                  "
                >
                  <el-input-number
                    v-model="finalScores[row.student_id].mark_obtained"
                    :min="0"
                    :max="100"
                    :step="1"
                    :precision="2"
                    :step-strictly="true"
                    size="default"
                    style="width: 120px"
                    @change="validateScore(row.student_id)"
                  />
                  <span style="color: #7f8c8d; font-weight: 500">/ 100</span>
                </div>
              </template>
            </el-table-column>

            <el-table-column label="Grade" width="100" align="center">
              <template #default="{ row }">
                <el-tag
                  :type="
                    getGradeType(finalScores[row.student_id].mark_obtained)
                  "
                  effect="dark"
                  size="default"
                >
                  {{ getGrade(finalScores[row.student_id].mark_obtained) }}
                </el-tag>
              </template>
            </el-table-column>
          </el-table>
        </div>

        <div class="card-footer">
          <div class="progress-info">
            <div class="progress-text">
              Progress: {{ completedScores }} of {{ students.length }} students
              completed
            </div>
            <el-progress
              :percentage="
                Math.round((completedScores / students.length) * 100)
              "
              :color="
                completedScores === students.length ? '#67c23a' : '#409eff'
              "
              class="progress-bar"
            />
          </div>
        </div>
      </el-card>
    </div>

    <!-- Empty State - No Students -->
    <div
      v-else-if="selectedCourseId && students.length === 0"
      class="empty-state"
    >
      <div class="select-course-prompt">
        <i class="el-icon-user-solid prompt-icon"></i>
        <h3 style="color: #2c3e50; margin-bottom: 8px">No Students Found</h3>
        <p>There are no students enrolled in this course yet.</p>
      </div>
    </div>

    <!-- Empty State - No Course Selected -->
    <div v-else class="empty-state">
      <div class="select-course-prompt">
        <i class="el-icon-document prompt-icon"></i>
        <h3 style="color: #2c3e50; margin-bottom: 8px">Select a Course</h3>
        <p>
          Choose a course from the dropdown above to start managing final exam
          scores.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";

// 当前用户
const user = JSON.parse(localStorage.getItem("user") || "{}");

// 状态管理
const myCourses = ref([]);
const selectedCourseId = ref(null);
const students = ref([]);
const finalScores = ref({});
const loading = ref(false);
const saving = ref(false);

// 计算属性
const completedScores = computed(() => {
  return Object.values(finalScores.value).filter(
    (score) =>
      score.mark_obtained !== null &&
      score.mark_obtained !== undefined &&
      score.mark_obtained !== ""
  ).length;
});

function validateScore(studentId) {
  const score = finalScores.value[studentId].mark_obtained;

  if (score < 0 || score > 100) {
    ElMessage.warning("Score must be between 0 and 100");
    finalScores.value[studentId].mark_obtained = null; // 清空
  }
}

// 获取教师课程
async function fetchCourses() {
  try {
    const res = await axios.get("http://localhost:8085/course-management");
    myCourses.value = res.data.data.filter((c) => c.lecturer_id === user.id);
  } catch {
    ElMessage.error("Failed to load courses");
  }
}

// 主函数：载入学生和已有分数
async function loadScores() {
  if (!selectedCourseId.value) return;

  loading.value = true;
  try {
    // 获取学生
    const stuRes = await axios.get(
      `http://localhost:8085/course-students?course_id=${selectedCourseId.value}`
    );
    students.value = stuRes.data.data || [];

    // 初始化分数字典
    finalScores.value = {};
    for (const stu of students.value) {
      finalScores.value[stu.student_id] = {
        student_id: stu.student_id,
        course_id: selectedCourseId.value,
        mark_obtained: null,
        id: null,
      };
    }

    // 加载已有分数
    const scoreRes = await axios.get(
      `http://localhost:8085/final-exam-scores?course_id=${selectedCourseId.value}`
    );
    const existing = scoreRes.data.data || [];
    for (const score of existing) {
      if (finalScores.value[score.student_id]) {
        finalScores.value[score.student_id] = {
          id: score.id,
          student_id: score.student_id,
          course_id: selectedCourseId.value,
          mark_obtained: score.mark_obtained,
        };
      }
    }
  } catch (error) {
    ElMessage.error("Failed to load final scores");
    console.error(error);
  } finally {
    loading.value = false;
  }
}

// 保存所有成绩
async function submitScores() {
  saving.value = true;
  try {
    const promises = [];
    for (const stu of students.value) {
      const score = finalScores.value[stu.student_id];
      if (
        score.mark_obtained !== null &&
        score.mark_obtained !== undefined &&
        score.mark_obtained !== ""
      ) {
        if (score.id) {
          promises.push(
            axios.put("http://localhost:8085/final-exam-scores", score)
          );
        } else {
          promises.push(
            axios.post("http://localhost:8085/final-exam-scores", score)
          );
        }
      }
    }

    await Promise.all(promises);
    ElMessage.success("Scores saved successfully!");
  } catch (error) {
    ElMessage.error("Failed to save scores");
    console.error(error);
  } finally {
    saving.value = false;
  }
}

// 清空所有分数
async function clearAllScores() {
  try {
    await ElMessageBox.confirm(
      "This will clear all entered scores. Are you sure?",
      "Clear All Scores",
      {
        confirmButtonText: "Yes, Clear All",
        cancelButtonText: "Cancel",
        type: "warning",
      }
    );

    for (const studentId in finalScores.value) {
      finalScores.value[studentId].mark_obtained = null;
    }

    ElMessage.success("All scores cleared");
  } catch {
    // User cancelled
  }
}

// 辅助函数
function getInitials(name) {
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .substring(0, 2);
}

function getGrade(score) {
  if (score === null || score === undefined || score === "") return "-";
  if (score >= 90) return "A+";
  if (score >= 80) return "A";
  if (score >= 75) return "A-";
  if (score >= 70) return "B+";
  if (score >= 65) return "B";
  if (score >= 60) return "B-";
  if (score >= 55) return "C+";
  if (score >= 50) return "C";
  if (score >= 45) return "C-";
  if (score >= 0) return "FAILED";
  return "F";
}

function getGradeType(score) {
  if (score === null || score === undefined || score === "") return "info";
  if (score >= 80) return "success";
  if (score >= 70) return "success";
  if (score >= 60) return "warning";
  if (score >= 50) return "warning";
  return "danger";
}

onMounted(fetchCourses);
</script>

<style scoped>
.assessment-management {
  padding: 24px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}

/* 页面头部 */
.page-header {
  margin-bottom: 32px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 32px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.title-section {
  flex: 1;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-icon {
  color: #409eff;
  font-size: 36px;
}

.page-subtitle {
  font-size: 16px;
  color: #7f8c8d;
  margin: 0;
}

.header-stats {
  display: flex;
  gap: 24px;
}

.stat-card {
  text-align: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 24px;
  border-radius: 12px;
  min-width: 100px;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
}

/* 控制面板 */
.control-panel {
  margin-bottom: 32px;
  border-radius: 16px;
  border: none;
}

.control-content {
  display: flex;
  justify-content: space-between;
  align-items: end;
  gap: 24px;
}

.course-selector {
  flex: 1;
  max-width: 400px;
}

.selector-label {
  display: block;
  font-weight: 500;
  color: #2c3e50;
  margin-bottom: 8px;
  font-size: 14px;
}

.course-select {
  width: 100%;
}

.course-option {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.course-name {
  font-weight: 500;
}

.course-code {
  color: #909399;
  font-size: 12px;
}

.add-button {
  height: 48px;
  padding: 0 24px;
  border-radius: 12px;
  font-weight: 500;
}

/* 组件网格 */
.components-section {
  margin-top: 32px;
}

.component-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
}

.component-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  padding: 24px 24px 16px;
  border-bottom: 1px solid #f0f2f5;
}

.component-info {
  flex: 1;
}

.component-title {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 8px 0;
}

.type-tag {
  font-size: 12px;
  font-weight: 500;
}

.component-actions {
  margin-left: 16px;
}

.card-content {
  padding: 0 24px 16px;
}

.component-details {
  margin-bottom: 20px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 14px;
}

.detail-icon {
  color: #409eff;
  font-size: 16px;
}

.detail-label {
  color: #7f8c8d;
  font-weight: 500;
}

.detail-value {
  color: #2c3e50;
  font-weight: 600;
}

.card-footer {
  padding: 16px 24px 24px;
  background: #fafbfc;
}

.progress-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.progress-text {
  font-size: 13px;
  color: #7f8c8d;
}

.progress-bar {
  flex: 1;
}

/* 空状态 */
.empty-state {
  margin-top: 60px;
}

.select-course-prompt {
  text-align: center;
  padding: 80px 20px;
  color: #7f8c8d;
}

.prompt-icon {
  font-size: 64px;
  color: #ddd;
  margin-bottom: 16px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .assessment-management {
    padding: 16px;
  }

  .header-content {
    flex-direction: column;
    gap: 24px;
    text-align: center;
  }

  .control-content {
    flex-direction: column;
    gap: 16px;
  }
}
</style>
