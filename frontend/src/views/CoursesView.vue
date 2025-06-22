<template>
  <div class="course-management">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="page-title">
            <i class="el-icon-document title-icon"></i>
            Course Management
          </h1>
          <p class="page-subtitle">
            Manage and organize all course information
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card">
            <div class="stat-number">{{ courses.length }}</div>
            <div class="stat-label">Total Courses</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ filteredCourses.length }}</div>
            <div class="stat-label">Filtered Results</div>
          </div>
        </div>
        <div
          class="header-actions"
          v-if="user.role === 'admin' || user.role === 'lecturer'"
        >
          <el-button type="primary" class="add-course-btn" @click="goAddCourse">
            <i class="el-icon-plus"></i>
            Add Course
          </el-button>
        </div>
      </div>
    </div>

    <!-- Control Panel -->
    <el-card class="control-panel">
      <div class="filter-section">
        <div class="filter-row">
          <div class="filter-group">
            <label class="filter-label">Search Courses</label>
            <el-input
              v-model="search"
              placeholder="Search by course name, code or lecturer"
              clearable
              prefix-icon="el-icon-search"
              class="search-input"
            />
          </div>

          <div class="filter-group">
            <label class="filter-label">Lecturer</label>
            <el-select
              v-model="lecturerFilter"
              placeholder="Filter by Lecturer"
              clearable
              class="filter-select"
            >
              <el-option
                v-for="lecturer in lecturers"
                :key="lecturer.id"
                :label="lecturer.name"
                :value="lecturer.id"
              />
            </el-select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Academic Year</label>
            <el-select
              v-model="yearFilter"
              placeholder="Academic Year"
              clearable
              class="filter-select"
            >
              <el-option
                v-for="year in academicYears"
                :key="year"
                :label="year"
                :value="year"
              />
            </el-select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Credit Hours</label>
            <el-select
              v-model="creditFilter"
              placeholder="Credit Hours"
              clearable
              class="filter-select"
            >
              <el-option
                v-for="c in [2, 3, 4]"
                :key="c"
                :label="`${c} Credits`"
                :value="c"
              />
            </el-select>
          </div>

          <div class="filter-group">
            <label class="filter-label">View Mode</label>
            <el-button
              @click="toggleView"
              :icon="
                viewMode === 'table' ? 'el-icon-collection' : 'el-icon-menu'
              "
              class="view-toggle-btn"
            >
              {{ viewMode === "table" ? "Card View" : "Table View" }}
            </el-button>
          </div>
        </div>
      </div>
    </el-card>

    <!-- Content Section -->
    <div class="content-section">
      <!-- Table View -->
      <div v-if="viewMode === 'table'" class="table-view">
        <el-card class="table-card">
          <div class="table-header">
            <div class="table-title">
              <i class="el-icon-menu table-icon"></i>
              Courses Table View
            </div>
            <div class="table-count">{{ filteredCourses.length }} courses</div>
          </div>

          <el-table
            :data="filteredCourses"
            stripe
            class="custom-table"
            v-loading="loading"
          >
            <el-table-column
              prop="course_name"
              label="Course Name"
              min-width="200"
            >
              <template #default="{ row }">
                <div class="course-name-cell">
                  <div class="course-icon">
                    <i class="el-icon-document"></i>
                  </div>
                  <div class="course-info">
                    <div class="course-name">{{ row.course_name }}</div>
                    <div class="course-code">{{ row.course_code }}</div>
                  </div>
                </div>
              </template>
            </el-table-column>

            <el-table-column label="Lecturer" min-width="150">
              <template #default="{ row }">
                <div class="lecturer-cell">
                  <div class="lecturer-avatar">
                    <i class="el-icon-user-solid"></i>
                  </div>
                  <span class="lecturer-name">{{
                    lecturerName(row.lecturer_id)
                  }}</span>
                </div>
              </template>
            </el-table-column>

            <el-table-column label="Academic Details" min-width="180">
              <template #default="{ row }">
                <div class="academic-details">
                  <div class="detail-item">
                    <span class="detail-label">Year:</span>
                    <span class="detail-value">{{ row.academic_year }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Semester:</span>
                    <span class="detail-value">{{ row.semester }}</span>
                  </div>
                </div>
              </template>
            </el-table-column>

            <el-table-column prop="credit_hours" label="Credits" width="100">
              <template #default="{ row }">
                <div class="credit-badge">{{ row.credit_hours }}</div>
              </template>
            </el-table-column>

            <el-table-column prop="status" label="Status" width="120">
              <template #default="{ row }">
                <el-tag
                  :type="row.status === 'active' ? 'success' : 'info'"
                  class="status-tag"
                >
                  {{ row.status }}
                </el-tag>
              </template>
            </el-table-column>

            <el-table-column label="Actions" width="180" align="center">
              <template #default="{ row }">
                <div
                  class="action-buttons"
                  v-if="user.role === 'admin' || user.role === 'lecturer'"
                >
                  <el-button
                    size="small"
                    type="primary"
                    class="edit-btn"
                    @click="editCourse(row.id)"
                  >
                    <i class="el-icon-edit"></i>
                    Edit
                  </el-button>
                  <el-button
                    size="small"
                    type="danger"
                    class="delete-btn"
                    @click="deleteCourse(row.id)"
                  >
                    <i class="el-icon-delete"></i>
                    Delete
                  </el-button>
                </div>
              </template>
            </el-table-column>
          </el-table>

          <div
            v-if="filteredCourses.length === 0 && !loading"
            class="empty-state"
          >
            <div class="empty-content">
              <i class="el-icon-document-remove empty-icon"></i>
              <h3 class="empty-title">No Courses Found</h3>
              <p class="empty-description">
                Try adjusting your search criteria or add a new course.
              </p>
            </div>
          </div>
        </el-card>
      </div>

      <!-- Card View -->
      <div v-else class="card-view">
        <div class="cards-header">
          <div class="cards-title">
            <i class="el-icon-collection cards-icon"></i>
            Courses Card View
          </div>
          <div class="cards-count">{{ filteredCourses.length }} courses</div>
        </div>

        <div class="cards-grid" v-loading="loading">
          <div
            v-for="course in filteredCourses"
            :key="course.id"
            class="course-card-wrapper"
          >
            <div class="course-card">
              <div class="card-header">
                <div class="course-header">
                  <h3 class="course-title">{{ course.course_name }}</h3>
                  <div class="course-code-badge">{{ course.course_code }}</div>
                </div>
                <el-tag
                  :type="course.status === 'active' ? 'success' : 'info'"
                  class="status-tag"
                >
                  {{ course.status }}
                </el-tag>
              </div>

              <div class="card-content">
                <div class="course-details">
                  <div class="detail-row">
                    <div class="detail-item">
                      <i class="el-icon-user-solid detail-icon"></i>
                      <div class="detail-info">
                        <span class="detail-label">Lecturer</span>
                        <span class="detail-value">{{
                          lecturerName(course.lecturer_id)
                        }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="detail-row">
                    <div class="detail-item">
                      <i class="el-icon-medal detail-icon"></i>
                      <div class="detail-info">
                        <span class="detail-label">Credit Hours</span>
                        <span class="detail-value"
                          >{{ course.credit_hours }} Credits</span
                        >
                      </div>
                    </div>
                  </div>

                  <div class="detail-row">
                    <div class="detail-item">
                      <i class="el-icon-calendar detail-icon"></i>
                      <div class="detail-info">
                        <span class="detail-label">Academic Year</span>
                        <span class="detail-value">{{
                          course.academic_year
                        }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="detail-row">
                    <div class="detail-item">
                      <i class="el-icon-time detail-icon"></i>
                      <div class="detail-info">
                        <span class="detail-label">Semester</span>
                        <span class="detail-value">{{ course.semester }}</span>
                      </div>
                    </div>
                  </div>

                  <div v-if="course.description" class="description-section">
                    <div class="description-label">Description</div>
                    <div class="description-text">{{ course.description }}</div>
                  </div>
                </div>
              </div>

              <div
                class="card-footer"
                v-if="user.role === 'admin' || user.role === 'lecturer'"
              >
                <div class="card-action-buttons">
                  <el-button
                    type="primary"
                    class="edit-card-btn"
                    @click="editCourse(course.id)"
                  >
                    <i class="el-icon-edit"></i>
                    Edit Course
                  </el-button>
                  <el-button
                    type="danger"
                    class="delete-card-btn"
                    @click="deleteCourse(course.id)"
                  >
                    <i class="el-icon-delete"></i>
                    Delete Course
                  </el-button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="filteredCourses.length === 0 && !loading"
          class="empty-state"
        >
          <div class="empty-content">
            <i class="el-icon-document-remove empty-icon"></i>
            <h3 class="empty-title">No Courses Found</h3>
            <p class="empty-description">
              Try adjusting your search criteria or add a new course.
            </p>
          </div>
        </div>
      </div>
    </div>
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
function editCourse(courseId) {
  router.push({ name: "EditCourse", params: { id: courseId } });
}

onMounted(() => {
  requestAnimationFrame(() => {
    fetchCourses();
    fetchLecturers();
  });
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
  gap: 24px;
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

.header-actions {
  display: flex;
}

.add-course-btn {
  height: 48px;
  padding: 0 24px;
  border-radius: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Control Panel */
.control-panel {
  margin-bottom: 32px;
  border-radius: 16px;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.filter-section {
  padding: 8px 0;
}

.filter-row {
  display: flex;
  gap: 20px;
  align-items: end;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 200px;
}

.filter-label {
  font-weight: 500;
  color: #2c3e50;
  font-size: 14px;
}

.search-input,
.filter-select {
  width: 100%;
}

.view-toggle-btn {
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Content Section */
.content-section {
  margin-top: 32px;
}

/* Table View */
.table-card {
  border-radius: 16px;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f2f5;
  margin-bottom: 20px;
}

.table-title {
  font-size: 20px;
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-icon {
  color: #409eff;
  font-size: 24px;
}

.table-count {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.custom-table {
  border-radius: 12px;
  overflow: hidden;
}

.course-name-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.course-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

.course-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.course-name {
  font-weight: 500;
  color: #2c3e50;
}

.course-code {
  font-size: 12px;
  color: #7f8c8d;
  background: #f8f9fa;
  padding: 2px 8px;
  border-radius: 12px;
}

.lecturer-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.lecturer-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
}

.lecturer-name {
  font-weight: 500;
  color: #2c3e50;
}

.academic-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item {
  display: flex;
  gap: 8px;
  font-size: 13px;
}

.detail-label {
  color: #7f8c8d;
  font-weight: 500;
}

.detail-value {
  color: #2c3e50;
  font-weight: 600;
}

.credit-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
  text-align: center;
}

.status-tag {
  border-radius: 16px;
  font-weight: 500;
}
.edit-btn {
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.delete-btn {
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Card View */
.cards-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  background: white;
  padding: 20px 24px;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}

.cards-title {
  font-size: 20px;
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 8px;
}

.cards-icon {
  color: #409eff;
  font-size: 24px;
}

.cards-count {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 24px;
}

.course-card-wrapper {
  display: flex;
}

.course-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
  width: 100%;
  display: flex;
  flex-direction: column;
}

.course-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.card-header {
  padding: 24px 24px 16px;
  border-bottom: 1px solid #f0f2f5;
  display: flex;
  justify-content: space-between;
  align-items: start;
}

.course-header {
  flex: 1;
  margin-right: 16px;
}

.course-title {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.course-code-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
}

.card-content {
  padding: 16px 24px;
  flex: 1;
}

.course-details {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  align-items: center;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.detail-icon {
  color: #409eff;
  font-size: 16px;
  width: 16px;
}

.detail-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.detail-label {
  font-size: 12px;
  color: #7f8c8d;
  font-weight: 500;
}

.detail-value {
  font-size: 14px;
  color: #2c3e50;
  font-weight: 600;
}

.description-section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #f0f2f5;
}

.description-label {
  font-size: 12px;
  color: #7f8c8d;
  font-weight: 500;
  margin-bottom: 8px;
}

.description-text {
  font-size: 14px;
  color: #2c3e50;
  line-height: 1.5;
}

.card-footer {
  padding: 16px 24px 24px;
  background: #fafbfc;
}

.card-action-buttons {
  display: flex;
  gap: 12px; /* 按钮之间的间隔 */
}

.edit-card-btn,
.delete-card-btn {
  flex: 1; /* 自动平均宽度 */
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

/* Empty State */
.empty-state {
  padding: 60px 20px;
  text-align: center;
}

.empty-content {
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

/* Responsive Design */
@media (max-width: 1200px) {
  .cards-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
}

@media (max-width: 768px) {
  .course-management {
    padding: 16px;
  }

  .header-content {
    flex-direction: column;
    gap: 24px;
    text-align: center;
  }

  .filter-row {
    flex-direction: column;
    gap: 16px;
  }

  .filter-group {
    min-width: 100%;
  }

  .cards-grid {
    grid-template-columns: 1fr;
  }

  .header-stats {
    width: 100%;
    justify-content: center;
  }
}
.action-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
}
</style>
