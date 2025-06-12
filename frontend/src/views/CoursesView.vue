<template>
  <div class="course-management">
    <el-card class="main-card">
      <div class="header">
        <h2>Course Management</h2>
        <el-button
          type="primary"
          @click="goAddCourse"
          v-if="user.role === 'admin'"
        >
          + Add Course
        </el-button>
      </div>

      <!-- 筛选区 -->
      <div class="filter-section">
        <el-row :gutter="20">
          <el-col :span="8">
            <el-input
              v-model="search"
              placeholder="Search by course name, code or lecturer"
              clearable
              prefix-icon="el-icon-search"
            />
          </el-col>
          <el-col :span="8">
            <el-select
              v-model="lecturerFilter"
              placeholder="Filter by Lecturer"
              clearable
              style="width: 100%"
            >
              <el-option
                v-for="lecturer in lecturers"
                :key="lecturer.id"
                :label="lecturer.name"
                :value="lecturer.id"
              />
            </el-select>
          </el-col>
          <el-col :span="4">
            <el-select
              v-model="yearFilter"
              placeholder="Academic Year"
              clearable
              style="width: 100%"
            >
              <el-option
                v-for="year in academicYears"
                :key="year"
                :label="year"
                :value="year"
              />
            </el-select>
          </el-col>
          <el-col :span="4">
            <el-select
              v-model="creditFilter"
              placeholder="Credit Hours"
              clearable
              style="width: 100%"
            >
              <el-option
                v-for="c in [2, 3, 4]"
                :key="c"
                :label="c"
                :value="c"
              />
            </el-select>
          </el-col>
          <el-col :span="4">
            <el-button
              @click="toggleView"
              :icon="
                viewMode === 'table' ? 'el-icon-collection' : 'el-icon-menu'
              "
            >
              {{ viewMode === "table" ? "Card View" : "Table View" }}
            </el-button>
          </el-col>
        </el-row>
      </div>

      <!-- 列表视图 -->
      <div v-if="viewMode === 'table'">
        <el-table
          :data="filteredCourses"
          stripe
          style="width: 100%"
          v-loading="loading"
        >
          <el-table-column prop="course_name" label="Course Name" />
          <el-table-column prop="course_code" label="Course Code" />
          <el-table-column label="Lecturer">
            <template #default="scope">
              {{ lecturerName(scope.row.lecturer_id) }}
            </template>
          </el-table-column>
          <el-table-column prop="academic_year" label="Academic Year" />
          <el-table-column prop="semester" label="Semester" />
          <el-table-column prop="credit_hours" label="Credit Hours" />
          <el-table-column prop="status" label="Status" />
          <el-table-column label="Actions" width="220">
            <template #default="scope">
              <el-tag
                :type="scope.row.status === 'active' ? 'success' : 'info'"
                style="margin-right: 8px"
              >
                {{ scope.row.status }}
              </el-tag>
              <el-button
                size="small"
                type="danger"
                @click="deleteCourse(scope.row.id)"
                v-if="user.role === 'admin'"
                >Delete</el-button
              >
            </template>
          </el-table-column>
        </el-table>
        <div v-if="filteredCourses.length === 0 && !loading" class="no-data">
          <el-empty description="No courses found" />
        </div>
      </div>

      <!-- 卡片视图 -->
      <div v-else class="card-view">
        <el-row :gutter="20" v-loading="loading">
          <el-col v-for="course in filteredCourses" :key="course.id" :span="8">
            <el-card class="course-card" shadow="hover">
              <div class="card-content">
                <h3 class="course-title">{{ course.course_name }}</h3>
                <p class="course-detail">
                  <b>Course Code:</b> {{ course.course_code }}<br />
                  <b>Lecturer:</b> {{ lecturerName(course.lecturer_id) }}<br />
                  <b>Credit Hours:</b> {{ course.credit_hours }}<br />
                  <b>Academic Year:</b> {{ course.academic_year }}<br />
                  <b>Semester:</b> {{ course.semester }}<br />
                  <b>Status:</b> {{ course.status }}<br />
                  <b>Description:</b> {{ course.description || "N/A" }}<br />
                </p>
                <el-button
                  size="small"
                  type="danger"
                  @click="deleteCourse(course.id)"
                  v-bind:data-allow-mismatch="true"
                  v-if="user.role === 'admin'"
                  >Delete</el-button
                >
              </div>
            </el-card>
          </el-col>
        </el-row>
        <div v-if="filteredCourses.length === 0 && !loading" class="no-data">
          <el-empty description="No courses found" />
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";

const router = useRouter();
const search = ref("");
const lecturerFilter = ref("");
const courses = ref([]);
const lecturers = ref([]);
const loading = ref(false);
const viewMode = ref("table"); // 'table' or 'card'
const yearFilter = ref("");
const creditFilter = ref("");
const user = JSON.parse(localStorage.getItem("user") || "{}");

const academicYears = Array.from({ length: 5 }, (_, i) => {
  const current = new Date().getFullYear();
  const base = new Date().getMonth() >= 8 ? current : current - 1;
  const start = base - 2 + i;
  return `${start}/${start + 1}`;
});

const fetchCourses = async () => {
  loading.value = true;
  try {
    const res = await axios.get("http://localhost:8085/course-management");
    if (res.data.success) {
      courses.value = res.data.data;
    } else {
      courses.value = [];
    }
  } catch (e) {
    courses.value = [];
  } finally {
    loading.value = false;
  }
};

// 获取所有讲师 getLecturers
const fetchLecturers = async () => {
  try {
    const res = await axios.get(
      "http://localhost:8085/user-management?role=lecturer"
    );
    if (res.data.success) {
      lecturers.value = res.data.users;
    } else {
      lecturers.value = [];
    }
  } catch (e) {
    lecturers.value = [];
  }
};

onMounted(() => {
  fetchCourses();
  fetchLecturers();
});

// 讲师ID转讲师名 lecturerid convert to  lecturer name
function lecturerName(id) {
  const arr = Array.isArray(lecturers.value) ? lecturers.value : [];
  const user = arr.find((u) => u.id == id);
  return user && user.name ? String(user.name) : String(id);
}

// 搜索和筛选 search and filter
const filteredCourses = computed(() => {
  let list = [...courses.value];

  if (search.value.trim()) {
    const keyword = search.value.toLowerCase();
    list = list.filter(
      (c) =>
        c.course_name.toLowerCase().includes(keyword) ||
        c.course_code.toLowerCase().includes(keyword) ||
        lecturerName(c.lecturer_id).toLowerCase().includes(keyword)
    );
  }

  if (lecturerFilter.value) {
    list = list.filter((c) => c.lecturer_id == lecturerFilter.value);
  }

  if (yearFilter.value) {
    list = list.filter((c) => c.academic_year === yearFilter.value);
  }

  if (creditFilter.value) {
    list = list.filter((c) => c.credit_hours == creditFilter.value);
  }

  return list;
});

function toggleView() {
  viewMode.value = viewMode.value === "table" ? "card" : "table";
}

function goAddCourse() {
  router.push({ name: "AddCourse" });
}

async function deleteCourse(id) {
  ElMessageBox.confirm("Are you sure to delete this course?", "Warning", {
    type: "warning",
  })
    .then(() =>
      axios.delete("http://localhost:8085/course-management", {
        data: { id },
      })
    )
    .then((res) => {
      if (res.data.success) {
        ElMessage.success("Course deleted");
        fetchCourses();
      } else {
        ElMessage.error(res.data.message || "Failed to delete");
      }
    })
    .catch(() => {});
}
</script>

<style scoped>
.course-management {
  padding: 20px;
}

.main-card {
  min-height: 600px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filter-section {
  margin-bottom: 20px;
  padding: 20px;
  background: #f5f7fa;
  border-radius: 8px;
}

.card-view {
  margin-top: 20px;
}

.course-card {
  margin-bottom: 20px;
  transition: all 0.3s;
}

.course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.card-content {
  padding: 10px;
}

.course-title {
  font-size: 18px;
  color: #409eff;
  margin-bottom: 8px;
  font-weight: 600;
}

.course-detail {
  font-size: 14px;
  color: #606266;
  margin-bottom: 12px;
}

.no-data {
  text-align: center;
  padding: 50px 0;
}

@media (max-width: 768px) {
  .filter-section .el-col {
    margin-bottom: 10px;
  }
  .card-view .el-col {
    margin-bottom: 20px;
  }
}
.stat-cards {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.stat-card {
  width: 200px;
  text-align: center;
  background: #ecf5ff;
}

.stat-title {
  font-weight: 500;
  color: #409eff;
}

.stat-number {
  font-size: 28px;
  font-weight: bold;
  margin-top: 10px;
  color: #303133;
}
</style>
