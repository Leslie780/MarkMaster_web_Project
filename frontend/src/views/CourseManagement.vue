<template>
  <div class="enrollment-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="page-title">
            <i class="el-icon-user-solid title-icon"></i>
            Course Enrollment Management
          </h1>
          <p class="page-subtitle">
            Manage student enrollments across your courses
          </p>
        </div>
        <div class="header-stats" v-if="selectedCourseId">
          <div class="stat-card">
            <div class="stat-number">{{ enrolledStudents.length }}</div>
            <div class="stat-label">Enrolled Students</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ availableStudents.length }}</div>
            <div class="stat-label">Available Students</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Control Panel -->
    <el-card class="control-panel">
      <div class="control-content">
        <div class="course-selector">
          <label class="selector-label">Select Course</label>
          <el-select
            v-model="selectedCourseId"
            placeholder="Choose a course to manage"
            class="course-select"
            @change="fetchEnrolledStudents"
          >
            <el-option
              v-for="course in myCourses"
              :key="course.id"
              :label="course.course_name"
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
      </div>
    </el-card>

    <!-- Enrollment Management Section -->
    <div v-if="selectedCourseId" class="enrollment-section">
      <!-- Add Student Panel -->
      <el-card class="add-student-panel">
        <div class="panel-header">
          <div class="panel-title">
            <i class="el-icon-plus panel-icon"></i>
            Enroll New Student
          </div>
        </div>
        <div class="panel-content">
          <div class="add-student-form">
            <div class="student-selector">
              <label class="form-label">Select Student</label>
              <el-select
                v-model="selectedStudentId"
                placeholder="Search and select a student"
                filterable
                class="student-select"
              >
                <el-option
                  v-for="student in availableStudents"
                  :key="student.id"
                  :label="`${student.name} (${student.matric_no})`"
                  :value="student.id"
                >
                  <div class="student-option">
                    <div class="student-info">
                      <span class="student-name">{{ student.name }}</span>
                      <span class="student-details"
                        >{{ student.matric_no }} • {{ student.email }}</span
                      >
                    </div>
                  </div>
                </el-option>
              </el-select>
            </div>
            <el-button
              type="primary"
              class="enroll-button"
              @click="enrollStudent"
              :disabled="!selectedStudentId"
              :loading="enrollLoading"
            >
              <i class="el-icon-check"></i>
              Enroll Student
            </el-button>
          </div>
        </div>
      </el-card>

      <!-- Students List -->
      <el-card class="students-list-card">
        <div class="card-header">
          <div class="section-title">
            <i class="el-icon-user section-icon"></i>
            Enrolled Students
          </div>
          <div class="student-count">
            {{ enrolledStudents.length }} students
          </div>
        </div>

        <div v-if="enrolledStudents.length > 0" class="students-table">
          <el-table :data="enrolledStudents" stripe class="custom-table">
            <el-table-column label="Student Name" prop="name" min-width="200">
              <template #default="{ row }">
                <div class="student-cell">
                  <div class="student-avatar">
                    <i class="el-icon-user-solid"></i>
                  </div>
                  <div class="student-details">
                    <div class="student-name">{{ row.name }}</div>
                    <div class="student-email">{{ row.email }}</div>
                  </div>
                </div>
              </template>
            </el-table-column>

            <el-table-column
              label="Matric Number"
              prop="matric_no"
              min-width="150"
            >
              <template #default="{ row }">
                <div class="matric-cell">
                  <span class="matric-number">{{ row.matric_no }}</span>
                </div>
              </template>
            </el-table-column>

            <el-table-column label="Actions" width="120" align="center">
              <template #default="{ row }">
                <el-button
                  type="danger"
                  size="small"
                  class="remove-button"
                  @click="removeStudent(row.student_id)"
                >
                  <i class="el-icon-delete"></i>
                  Remove
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>

        <div v-else class="empty-state">
          <div class="empty-content">
            <i class="el-icon-user-solid empty-icon"></i>
            <h3 class="empty-title">No Students Enrolled</h3>
            <p class="empty-description">
              Start by enrolling students to this course using the form above.
            </p>
          </div>
        </div>
      </el-card>
    </div>

    <!-- Select Course Prompt -->
    <div v-else class="select-course-prompt">
      <div class="prompt-content">
        <i class="el-icon-folder-opened prompt-icon"></i>
        <h3 class="prompt-title">Select a Course</h3>
        <p class="prompt-description">
          Please select a course from the dropdown above to manage student
          enrollments.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { ElMessage } from "element-plus";

const user = JSON.parse(localStorage.getItem("user") || "{}");

const myCourses = ref([]);
const selectedCourseId = ref(null);
const enrolledStudents = ref([]);
const availableStudents = ref([]);
const selectedStudentId = ref(null);
const enrollLoading = ref(false);

// 获取当前讲师的课程
async function fetchCourses() {
  try {
    const res = await axios.get("http://localhost:8085/course-management");
    if (res.data.success) {
      myCourses.value = res.data.data.filter((c) => c.lecturer_id === user.id);
    }
  } catch (err) {
    ElMessage.error("Failed to load courses");
  }
}

// 获取课程学生 + 可选学生
async function fetchEnrolledStudents() {
  if (!selectedCourseId.value) return;

  try {
    const [enrolledRes, availableRes] = await Promise.all([
      axios.get(
        `http://localhost:8085/course-students?course_id=${selectedCourseId.value}`
      ),
      axios.get("http://localhost:8085/user-management?role=student"),
    ]);

    if (enrolledRes.data.success) {
      enrolledStudents.value = enrolledRes.data.data;
    } else {
      enrolledStudents.value = [];
    }

    if (availableRes.data.success) {
      availableStudents.value = availableRes.data.users.filter(
        (stu) =>
          !enrolledStudents.value.some((enr) => enr.student_id === stu.id)
      );
    }
  } catch (err) {
    ElMessage.error("Failed to fetch data");
  }
}

// 添加学生到课程
async function enrollStudent() {
  const alreadyEnrolled = enrolledStudents.value.some(
    (student) => student.student_id === selectedStudentId.value
  );

  if (alreadyEnrolled) {
    ElMessage.warning("Student already enrolled");
    return;
  }

  enrollLoading.value = true;
  try {
    const res = await axios.post("http://localhost:8085/course-students", {
      student_id: selectedStudentId.value,
      course_id: selectedCourseId.value,
    });

    if (res.data.success) {
      ElMessage.success("Student enrolled successfully");
      selectedStudentId.value = null;
      await fetchEnrolledStudents();
    } else {
      ElMessage.error(res.data.message);
    }
  } catch (err) {
    ElMessage.error("Enrollment failed");
  } finally {
    enrollLoading.value = false;
  }
}

// 移除学生
async function removeStudent(studentId) {
  try {
    const res = await axios.post("http://localhost:8085/course-students", {
      student_id: studentId,
      course_id: selectedCourseId.value,
      action: "delete",
    });

    if (res.data.success) {
      ElMessage.success("Student removed from course");
      await fetchEnrolledStudents();
    } else {
      ElMessage.error(res.data.message || "Failed to remove student");
    }
  } catch (err) {
    ElMessage.error("Error removing student");
  }
}

onMounted(() => {
  fetchCourses();
});
</script>

<style scoped>
.enrollment-container {
  padding: 24px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}

/* Page Header */
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

/* Control Panel */
.control-panel {
  margin-bottom: 32px;
  border-radius: 16px;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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
  font-weight: 600;
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
  width: 100%;
}

.course-name {
  font-weight: 500;
  color: #2c3e50;
}

.course-code {
  color: #909399;
  font-size: 12px;
}

/* Enrollment Section */
.enrollment-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Add Student Panel */
.add-student-panel {
  border-radius: 16px;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.panel-header {
  margin-bottom: 20px;
}

.panel-title {
  font-size: 20px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.panel-icon {
  font-size: 24px;
}

.panel-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-top: 16px;
}

.add-student-form {
  display: flex;
  gap: 16px;
  align-items: end;
}

.student-selector {
  flex: 1;
}

.form-label {
  display: block;
  font-weight: 500;
  color: #2c3e50;
  margin-bottom: 8px;
  font-size: 14px;
}

.student-select {
  width: 100%;
}

.student-option {
  padding: 8px 0;
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.student-name {
  font-weight: 500;
  color: #2c3e50;
}

.student-details {
  font-size: 12px;
  color: #7f8c8d;
}

.enroll-button {
  height: 48px;
  padding: 0 24px;
  border-radius: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Students List Card */
.students-list-card {
  border-radius: 16px;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f2f5;
  margin-bottom: 20px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-icon {
  color: #409eff;
  font-size: 24px;
}

.student-count {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

/* Custom Table */
.custom-table {
  border-radius: 12px;
  overflow: hidden;
}

.student-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.student-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

.student-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.student-name {
  font-weight: 500;
  color: #2c3e50;
}

.student-email {
  font-size: 12px;
  color: #7f8c8d;
}

.matric-cell {
  display: flex;
  align-items: center;
}

.matric-number {
  background: #f8f9fa;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 13px;
  font-weight: 500;
  color: #495057;
}

.remove-button {
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Empty State */
.empty-state {
  padding: 60px 20px;
}

.empty-content {
  text-align: center;
  color: #7f8c8d;
}

.empty-icon {
  font-size: 64px;
  color: #ddd;
  margin-bottom: 16px;
}

.empty-title {
  font-size: 20px;
  color: #2c3e50;
  margin-bottom: 8px;
}

.empty-description {
  font-size: 14px;
  color: #7f8c8d;
  margin: 0;
}

/* Select Course Prompt */
.select-course-prompt {
  margin-top: 60px;
}

.prompt-content {
  text-align: center;
  padding: 80px 20px;
  color: #7f8c8d;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.prompt-icon {
  font-size: 64px;
  color: #ddd;
  margin-bottom: 16px;
}

.prompt-title {
  font-size: 24px;
  color: #2c3e50;
  margin-bottom: 8px;
}

.prompt-description {
  font-size: 16px;
  color: #7f8c8d;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .enrollment-container {
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

  .add-student-form {
    flex-direction: column;
    gap: 16px;
  }

  .enroll-button {
    width: 100%;
    justify-content: center;
  }

  .header-stats {
    width: 100%;
    justify-content: center;
  }
}
</style>
