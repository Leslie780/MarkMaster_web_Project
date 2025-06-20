<template>
  <div class="assessment-management">
    <!-- 主标题区域 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="page-title">
            <el-icon class="title-icon"><Document /></el-icon>
            Assessment Management
          </h1>
          <p class="page-subtitle">
            Manage your course assessments and grade students
          </p>
        </div>
        <div class="header-stats" v-if="selectedCourseId">
          <div class="stat-card">
            <div class="stat-number">{{ components.length }}</div>
            <div class="stat-label">Components</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ totalStudentsGraded }}</div>
            <div class="stat-label">Graded</div>
          </div>
        </div>
      </div>
    </div>

    <!-- 操作区域 -->
    <el-card class="control-panel" shadow="hover">
      <div class="control-content">
        <div class="course-selector">
          <label class="selector-label">Select Course</label>
          <el-select
            v-model="selectedCourseId"
            placeholder="Choose a course to manage"
            size="large"
            class="course-select"
            @change="fetchComponents"
          >
            <el-option
              v-for="course in myCourses"
              :key="course.id"
              :label="course.course_name"
              :value="course.id"
            >
              <div class="course-option">
                <span class="course-name">{{ course.course_name }}</span>
                <span class="course-code">{{ course.course_code }}</span>
              </div>
            </el-option>
          </el-select>
        </div>

        <el-button
          type="primary"
          size="large"
          :disabled="!selectedCourseId"
          @click="openDialog()"
          class="add-button"
        >
          <el-icon><Plus /></el-icon>
          Add New Component
        </el-button>
      </div>
    </el-card>

    <!-- 组件列表 -->
    <div v-if="selectedCourseId" class="components-section">
      <div v-if="components.length > 0" class="components-grid">
        <div
          v-for="component in components"
          :key="component.id"
          class="component-card"
        >
          <div class="card-header">
            <div class="component-info">
              <h3 class="component-title">{{ component.title }}</h3>
              <el-tag
                :type="getTypeColor(component.type)"
                effect="light"
                class="type-tag"
              >
                {{ component.type }}
              </el-tag>
            </div>
            <div class="component-actions">
              <el-dropdown trigger="click">
                <el-button text>
                  <el-icon><MoreFilled /></el-icon>
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item @click="openDialog(component)">
                      <el-icon><Edit /></el-icon>Edit
                    </el-dropdown-item>
                    <el-dropdown-item @click="openGradeDialog(component)">
                      <el-icon><Trophy /></el-icon>Grade
                    </el-dropdown-item>
                    <el-dropdown-item
                      @click="deleteComponent(component.id)"
                      divided
                    >
                      <el-icon><Delete /></el-icon>Delete
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </div>

          <div class="card-content">
            <div class="component-details">
              <div class="detail-item">
                <el-icon class="detail-icon"><Medal /></el-icon>
                <span class="detail-label">Max Mark:</span>
                <span class="detail-value">{{ component.max_mark }}</span>
              </div>
              <div class="detail-item">
                <el-icon class="detail-icon"><Percent /></el-icon>
                <span class="detail-label">Weight:</span>
                <span class="detail-value">{{ component.percentage }}%</span>
              </div>
              <div class="detail-item" v-if="component.due_date">
                <el-icon class="detail-icon"><Calendar /></el-icon>
                <span class="detail-label">Due:</span>
                <span class="detail-value">{{
                  formatDate(component.due_date)
                }}</span>
              </div>
            </div>

            <!-- 学生得分预览 -->
            <div class="scores-preview">
              <div class="scores-header">
                <span class="scores-title">Student Scores</span>
                <el-badge
                  :value="component.scores?.length || 0"
                  class="scores-count"
                />
              </div>
              <div
                v-if="component.scores && component.scores.length > 0"
                class="scores-list"
              >
                <div
                  v-for="score in component.scores.slice(0, 3)"
                  :key="score.id"
                  class="score-item"
                >
                  <span class="student-name">{{ score.student_name }}</span>
                  <span class="student-score"
                    >{{ score.mark_obtained }}/{{ component.max_mark }}</span
                  >
                </div>
                <div v-if="component.scores.length > 3" class="more-scores">
                  +{{ component.scores.length - 3 }} more students
                </div>
              </div>
              <div v-else class="no-scores">
                <el-icon><Warning /></el-icon>
                No scores recorded
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="progress-info">
              <span class="progress-text">
                {{ getGradedCount(component) }}/{{ getTotalStudents() }}
                students graded
              </span>
              <el-progress
                :percentage="getGradedPercentage(component)"
                :stroke-width="6"
                :show-text="false"
                class="progress-bar"
              />
            </div>
          </div>
        </div>
      </div>

      <el-empty
        v-else
        description="No assessment components yet"
        class="empty-state"
      >
        <el-button type="primary" @click="openDialog()">
          <el-icon><Plus /></el-icon>
          Create Your First Component
        </el-button>
      </el-empty>
    </div>

    <!-- 选择提示 -->
    <div v-else class="select-course-prompt">
      <el-icon class="prompt-icon"><FolderOpened /></el-icon>
      <h3>Select a Course</h3>
      <p>
        Choose a course from the dropdown above to start managing assessments
      </p>
    </div>

    <!-- 新增/编辑弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="
        form.id ? 'Edit Assessment Component' : 'Create Assessment Component'
      "
      width="600px"
      class="component-dialog"
    >
      <el-form :model="form" label-width="140px" class="component-form">
        <el-form-item label="Component Title" required>
          <el-input
            v-model="form.title"
            placeholder="e.g., Midterm Exam, Assignment 1"
            size="large"
          />
        </el-form-item>

        <el-form-item label="Assessment Type" required>
          <el-select
            v-model="form.type"
            placeholder="Select assessment type"
            size="large"
          >
            <el-option
              v-for="option in typeOptions"
              :key="option.value"
              :label="option.label"
              :value="option.value"
            />
          </el-select>
        </el-form-item>

        <div class="form-row">
          <el-form-item label="Maximum Mark" required>
            <el-input-number
              v-model="form.max_mark"
              :min="1"
              :max="1000"
              size="large"
              controls-position="right"
              style="width: 100%"
            />
          </el-form-item>

          <el-form-item label="Weight (%)" required>
            <el-input-number
              v-model="form.percentage"
              :min="1"
              :max="100"
              size="large"
              controls-position="right"
              style="width: 100%"
            />
          </el-form-item>
        </div>

        <el-form-item label="Due Date">
          <el-date-picker
            v-model="form.due_date"
            type="date"
            placeholder="Select due date"
            size="large"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button size="large" @click="dialogVisible = false"
            >Cancel</el-button
          >
          <el-button type="primary" size="large" @click="submitForm">
            {{ form.id ? "Update Component" : "Create Component" }}
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- 打分弹窗 -->
    <el-dialog
      v-model="gradeDialogVisible"
      title="Grade Students"
      width="800px"
      class="grading-dialog"
    >
      <div class="grading-header">
        <div class="component-info">
          <h3>{{ gradingComponent?.title }}</h3>
          <p>Maximum Mark: {{ gradingComponent?.max_mark }}</p>
        </div>
      </div>

      <el-table :data="students" class="grading-table" stripe>
        <el-table-column prop="matric_no" label="Matric No" width="120" />
        <el-table-column prop="name" label="Student Name" />
        <el-table-column label="Score" width="150">
          <template #default="{ row }">
            <el-input-number
              v-model="studentScores[row.student_id].mark_obtained"
              :min="0"
              :max="gradingComponent?.max_mark || 100"
              :step="1"
              size="small"
              controls-position="right"
            />
          </template>
        </el-table-column>
        <el-table-column label="Grade" width="80">
          <template #default="{ row }">
            <el-tag
              :type="
                getGradeColor(
                  studentScores[row.student_id]?.mark_obtained,
                  gradingComponent?.max_mark
                )
              "
            >
              {{
                calculateGrade(
                  studentScores[row.student_id]?.mark_obtained,
                  gradingComponent?.max_mark
                )
              }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>

      <template #footer>
        <div class="dialog-footer">
          <el-button size="large" @click="gradeDialogVisible = false"
            >Cancel</el-button
          >
          <el-button type="primary" size="large" @click="submitScores">
            Save All Scores
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";
import {
  Document,
  Plus,
  Edit,
  Delete,
  Trophy,
  Medal,
  Percent,
  Calendar,
  Warning,
  FolderOpened,
  MoreFilled,
} from "@element-plus/icons-vue";

const user = JSON.parse(localStorage.getItem("user") || "{}");

const selectedCourseId = ref(null);
const myCourses = ref([]);
const components = ref([]);

const dialogVisible = ref(false);
const form = ref({
  id: null,
  course_id: null,
  title: "",
  type: "",
  max_mark: 100,
  percentage: 10,
  due_date: "",
});

const typeOptions = [
  { value: "assignment", label: "Assignment" },
  { value: "quiz", label: "Quiz" },
  { value: "project", label: "Project" },
  { value: "lab", label: "Lab" },
  { value: "test", label: "Test" },
];

// 计算属性
const totalStudentsGraded = computed(() => {
  if (!components.value.length) return 0;
  return components.value.reduce((total, comp) => {
    return total + (comp.scores?.length || 0);
  }, 0);
});

// 方法
function getTypeColor(type) {
  const colors = {
    assignment: "primary",
    quiz: "success",
    project: "warning",
    test: "success",
    lab: "success",
  };
  return colors[type] || "primary";
}

function formatDate(date) {
  if (!date) return "-";
  return new Date(date).toLocaleDateString();
}

function getGradedCount(component) {
  return component.scores?.length || 0;
}

function getTotalStudents() {
  return students.value.length || 0;
}

function getGradedPercentage(component) {
  const total = getTotalStudents();
  if (total === 0) return 0;
  return Math.round((getGradedCount(component) / total) * 100);
}

function calculateGrade(score, maxMark) {
  if (!score || !maxMark) return "-";
  const percentage = (score / maxMark) * 100;
  if (percentage >= 90) return "A+";
  if (percentage >= 80) return "A";
  if (percentage >= 75) return "A-";
  if (percentage >= 70) return "B+";
  if (percentage >= 65) return "B";
  if (percentage >= 60) return "B-";
  if (percentage >= 55) return "C+";
  if (percentage >= 50) return "C";
  if (percentage >= 45) return "C-";
  if (percentage >= 0) return "FAILED";
  return "F";
}

function getGradeColor(score, maxMark) {
  const grade = calculateGrade(score, maxMark);
  const colors = {
    A: "success",
    B: "primary",
    C: "warning",
    D: "info",
    F: "danger",
  };
  return colors[grade] || "";
}

async function fetchCourses() {
  try {
    const res = await axios.get("http://localhost:8085/course-management");
    myCourses.value = res.data.data.filter((c) => c.lecturer_id === user.id);
  } catch {
    ElMessage.error("Failed to load courses");
  }
}

async function fetchComponents() {
  if (!selectedCourseId.value) return;
  try {
    const res = await axios.get(
      `http://localhost:8085/assessment-components?course_id=${selectedCourseId.value}`
    );
    const comps = res.data.data || [];

    // 获取学生列表用于匹配学生姓名
    const studentsRes = await axios.get(
      `http://localhost:8085/course-students?course_id=${selectedCourseId.value}`
    );
    const studentsList = studentsRes.data.data || [];

    for (const comp of comps) {
      const scoreRes = await axios.get(
        `http://localhost:8085/assessment-scores?course_id=${selectedCourseId.value}&component_id=${comp.id}`
      );
      const scores = scoreRes.data.data || [];

      // 为每个分数匹配学生姓名
      comp.scores = scores.map((score) => {
        const student = studentsList.find(
          (s) => s.student_id === score.student_id
        );
        return {
          ...score,
          student_name: student ? student.name : "Unknown Student",
        };
      });
    }

    components.value = comps;
  } catch (err) {
    ElMessage.error("Failed to fetch components or scores");
  }
}

function openDialog(component = null) {
  if (component) {
    form.value = { ...component };
  } else {
    form.value = {
      id: null,
      course_id: selectedCourseId.value,
      title: "",
      type: "",
      max_mark: 100,
      percentage: 10,
      due_date: "",
    };
  }
  dialogVisible.value = true;
}

async function submitForm() {
  try {
    const payload = { ...form.value };
    let res;

    if (payload.id) {
      res = await axios.put(
        "http://localhost:8085/assessment-components",
        payload
      );
    } else {
      res = await axios.post(
        "http://localhost:8085/assessment-components",
        payload
      );
    }

    if (res.data.success) {
      ElMessage.success("Saved successfully");
      dialogVisible.value = false;
      await fetchComponents();
    } else {
      ElMessage.error(res.data.message || "Save failed");
    }
  } catch {
    ElMessage.error("Error saving component");
  }
}

async function deleteComponent(id) {
  try {
    await ElMessageBox.confirm(
      "Are you sure you want to delete this component? This action cannot be undone.",
      "Delete Assessment Component",
      { type: "warning" }
    );
    const res = await axios.delete(
      "http://localhost:8085/assessment-components",
      { data: { id } }
    );
    if (res.data.success) {
      ElMessage.success("Component deleted successfully");
      await fetchComponents();
    } else {
      ElMessage.error(res.data.message);
    }
  } catch (error) {
    console.error("Delete failed", error);
  }
}

const gradeDialogVisible = ref(false);
const gradingComponent = ref(null);
const students = ref([]);
const studentScores = ref({});

async function openGradeDialog(component) {
  gradingComponent.value = component;
  gradeDialogVisible.value = true;
  await fetchStudentsAndScores();
}

async function fetchStudentsAndScores() {
  try {
    const res = await axios.get(
      `http://localhost:8085/course-students?course_id=${selectedCourseId.value}`
    );
    students.value = res.data.data || [];

    studentScores.value = {};
    for (const student of students.value) {
      studentScores.value[student.student_id] = {
        student_id: student.student_id,
        mark_obtained: null,
        id: null,
      };
    }

    const scoreRes = await axios.get(
      `http://localhost:8085/assessment-scores?course_id=${selectedCourseId.value}&component_id=${gradingComponent.value.id}`
    );
    const existingScores = scoreRes.data.data || [];

    for (const score of existingScores) {
      studentScores.value[score.student_id] = {
        id: score.id,
        student_id: score.student_id,
        mark_obtained: score.mark_obtained,
      };
    }
  } catch (error) {
    console.error("Error fetching students or scores:", error);
  }
}

async function submitScores() {
  try {
    for (const stu of students.value) {
      const enteredMark = studentScores.value[stu.student_id]?.mark_obtained;
      if (
        enteredMark !== undefined &&
        enteredMark !== null &&
        enteredMark !== ""
      ) {
        const existing = studentScores.value[stu.student_id];
        const payload = {
          student_id: stu.student_id,
          course_id: selectedCourseId.value,
          component_id: gradingComponent.value.id,
          mark_obtained: Number(enteredMark),
        };

        if (existing?.id) {
          payload.id = existing.id;
          await axios.put("http://localhost:8085/assessment-scores", payload);
        } else {
          await axios.post("http://localhost:8085/assessment-scores", payload);
        }
      }
    }
    ElMessage.success("All scores saved successfully");
    gradeDialogVisible.value = false;
    await fetchComponents();
  } catch {
    ElMessage.error("Failed to save scores");
  }
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

.components-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
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

.scores-preview {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 16px;
}

.scores-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.scores-title {
  font-weight: 500;
  color: #2c3e50;
  font-size: 14px;
}

.scores-list > .score-item:not(:last-child) {
  margin-bottom: 8px;
}

.score-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: white;
  border-radius: 8px;
  font-size: 13px;
}

.student-name {
  color: #2c3e50;
}

.student-score {
  font-weight: 600;
  color: #409eff;
}

.more-scores {
  text-align: center;
  color: #7f8c8d;
  font-size: 12px;
  padding: 8px;
}

.no-scores {
  text-align: center;
  color: #95a5a6;
  padding: 16px;
  font-size: 14px;
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

/* 弹窗样式 */
.component-dialog :deep(.el-dialog) {
  border-radius: 16px;
}

.component-form {
  padding: 0 8px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 16px;
}

.grading-dialog :deep(.el-dialog) {
  border-radius: 16px;
}

.grading-header {
  margin-bottom: 24px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
}

.grading-header h3 {
  margin: 0 0 4px 0;
  color: #2c3e50;
}

.grading-header p {
  margin: 0;
  color: #7f8c8d;
}

.grading-table {
  margin-bottom: 24px;
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

  .components-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
