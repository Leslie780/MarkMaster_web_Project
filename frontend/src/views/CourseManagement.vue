<template>
  <div class="enrollment-container">
    <el-card class="main-card">
      <div class="header">
        <h2>Manage Course Enrollments</h2>
      </div>

      <el-select
        v-model="selectedCourseId"
        placeholder="Select Course"
        style="width: 300px; margin-bottom: 20px"
        @change="fetchEnrolledStudents"
      >
        <el-option
          v-for="course in myCourses"
          :key="course.id"
          :label="course.course_name"
          :value="course.id"
        />
      </el-select>

      <div v-if="selectedCourseId">
        <el-row style="margin-bottom: 16px">
          <el-col :span="12">
            <el-select
              v-model="selectedStudentId"
              placeholder="Select student to enroll"
              filterable
              style="width: 100%"
            >
              <el-option
                v-for="student in availableStudents"
                :key="student.id"
                :label="`${student.name} (${student.matric_no})`"
                :value="student.id"
              />
            </el-select>
          </el-col>
          <el-col :span="4" style="margin-left: 10px">
            <el-button
              type="primary"
              @click="enrollStudent"
              :disabled="!selectedStudentId"
              :loading="enrollLoading"
            >
              Enroll
            </el-button>
          </el-col>
        </el-row>

        <el-table :data="enrolledStudents" stripe style="width: 100%">
          <el-table-column label="Name" prop="name" />
          <el-table-column label="Matric No" prop="matric_no" />
          <el-table-column label="Email" prop="email" />
        </el-table>

        <div v-if="enrolledStudents.length === 0" class="no-data">
          <el-empty description="No students enrolled in this course." />
        </div>
      </div>
    </el-card>
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
const enrollLoading = ref(false); // 新增

// 获取讲师的课程
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

// 获取课程中已选学生
async function fetchEnrolledStudents() {
  if (!selectedCourseId.value) return;
  try {
    const res = await axios.get(
      `http://localhost:8085/course-students?course_id=${selectedCourseId.value}`
    );
    if (res.data.success) {
      enrolledStudents.value = res.data.data;
    } else {
      enrolledStudents.value = [];
    }
    await fetchAvailableStudents(); // 更新下拉框
  } catch (err) {
    ElMessage.error("Failed to fetch enrolled students");
  }
}

// 获取学生列表并过滤掉已选学生
async function fetchAvailableStudents() {
  try {
    const res = await axios.get(
      "http://localhost:8085/user-management?role=student"
    );
    if (res.data.success) {
      availableStudents.value = res.data.users.filter(
        (stu) => !enrolledStudents.value.some((enr) => enr.id === stu.id)
      );
    }
  } catch (err) {
    ElMessage.error("Failed to load students");
  }
}

// 添加学生到课程，防止重复添加
async function enrollStudent() {
  const alreadyEnrolled = enrolledStudents.value.some(
    (student) => student.id === selectedStudentId.value
  );

  if (alreadyEnrolled) {
    ElMessage.warning("Student already enrolled");
    return;
  }

  enrollLoading.value = true; // 开始 loading
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
    enrollLoading.value = false; // 停止 loading
  }
}

// 移除学生
async function removeStudent(studentId) {
  try {
    const res = await axios.post(
      "http://localhost:8085/course-students",
      {
        student_id: studentId,
        course_id: selectedCourseId.value,
        action: "delete",
      },
      {
        headers: {
          "Content-Type": "application/json",
        },
      }
    );

    if (res.data.success) {
      ElMessage.success("Student removed");
      await fetchEnrolledStudents();
    } else {
      ElMessage.error(res.data.message);
    }
  } catch (err) {
    ElMessage.error(err.response?.data?.message || "Failed to remove student");
  }
}

// 初始加载
onMounted(() => {
  fetchCourses();
});
</script>

<style scoped>
.enrollment-container {
  padding: 20px;
}

.main-card {
  max-width: 900px;
  margin: auto;
}

.header {
  margin-bottom: 20px;
}

.no-data {
  margin-top: 20px;
}
</style>
