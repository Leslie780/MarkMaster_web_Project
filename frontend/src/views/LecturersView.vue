<template>
  <div class="lecturers-view">
    <h1 class="page-title">👨‍🏫 Lecturers</h1>

    <el-row :gutter="20" class="filter-bar">
      <el-col :span="6">
        <el-select
          v-model="selectedDepartment"
          placeholder="Filter by department"
          clearable
        >
          <el-option
            v-for="department in departments"
            :key="department"
            :label="department"
            :value="department"
          />
        </el-select>
      </el-col>

      <el-col :span="6">
        <el-input
          v-model="searchKeyword"
          placeholder="Search by name or department"
          clearable
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="card-list">
      <el-col
        v-for="lecturer in filteredLecturers"
        :key="lecturer.id"
        :span="8"
      >
        <el-card class="lecturer-card" shadow="hover">
          <div class="card-content">
            <el-avatar :src="lecturer.avatar" size="large" class="avatar" />
            <h3 class="lecturer-name">{{ lecturer.name }}</h3>
            <p class="lecturer-detail">{{ lecturer.department }}</p>
            <el-button
              size="small"
              type="primary"
              @click="openDialog(lecturer)"
            >
              View
            </el-button>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 弹窗：查看详情 -->
    <el-dialog
      v-model="dialogVisible"
      :title="selectedLecturer?.name"
      width="400px"
      class="dialog-style"
    >
      <div class="dialog-content" v-if="selectedLecturer">
        <el-avatar :src="selectedLecturer.avatar" size="large" class="avatar" />
        <p><strong>Email:</strong> {{ selectedLecturer.email }}</p>
        <p><strong>Faculty:</strong> {{ selectedLecturer.faculty }}</p>
        <p><strong>Department:</strong> {{ selectedLecturer.department }}</p>
        <p><strong>Bio:</strong> {{ selectedLecturer.bio }}</p>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Search } from "@element-plus/icons-vue";

const dialogVisible = ref(false);
const selectedLecturer = ref(null);
const selectedDepartment = ref("");
const searchKeyword = ref("");

const lecturers = ref([
  {
    id: 1,
    name: "Dr. Emily Tan",
    email: "emily.tan@univ.edu",
    faculty: "Faculty of Computing",
    department: "Software Engineering",
    avatar: "https://i.pravatar.cc/150?img=1",
    bio: "Expert in software engineering and AI education.",
  },
  {
    id: 2,
    name: "Mr. John Lee",
    email: "john.lee@univ.edu",
    faculty: "Faculty of Computing",
    department: "Information Systems",
    avatar: "https://i.pravatar.cc/150?img=2",
    bio: "Specialist in enterprise systems and ERP.",
  },
  {
    id: 3,
    name: "Prof. Sara Ng",
    email: "sara.ng@univ.edu",
    faculty: "Faculty of Computing",
    department: "Data Science",
    avatar: "https://i.pravatar.cc/150?img=3",
    bio: "Researcher in deep learning and predictive analytics.",
  },
  {
    id: 4,
    name: "Dr. Alan Wong",
    email: "alan.wong@univ.edu",
    faculty: "Faculty of Computing",
    department: "Computer Science",
    avatar: "https://i.pravatar.cc/150?img=4",
    bio: "Teaching algorithms and system design.",
  },
]);

const departments = [
  "Software Engineering",
  "Information Systems",
  "Data Science",
  "Computer Science",
];

const filteredLecturers = computed(() => {
  return lecturers.value.filter((lecturer) => {
    const matchDepartment =
      !selectedDepartment.value ||
      lecturer.department === selectedDepartment.value;
    const keyword = searchKeyword.value.toLowerCase();
    const matchKeyword =
      lecturer.name.toLowerCase().includes(keyword) ||
      lecturer.department.toLowerCase().includes(keyword);
    return matchDepartment && matchKeyword;
  });
});

const openDialog = (lecturer) => {
  selectedLecturer.value = lecturer;
  dialogVisible.value = true;
};
</script>

<style scoped>
.lecturers-view {
  background-color: #f9f6f1;
  padding: 24px;
  min-height: 100vh;
  color: #4a4a4a;
}

.page-title {
  font-size: 22px;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
}

.filter-bar {
  margin-bottom: 20px;
}

.card-list {
  margin-top: 10px;
}

.lecturer-card {
  background-color: #fff9f2;
  border-radius: 12px;
  border: 1px solid #e5ded7;
}

.card-content {
  text-align: center;
  padding: 16px;
}

.avatar {
  margin-bottom: 12px;
}

.lecturer-name {
  font-size: 18px;
  color: #d6a77a;
  margin-bottom: 4px;
}

.lecturer-detail {
  font-size: 14px;
  color: #666;
  margin-bottom: 12px;
}

.el-button--primary {
  background-color: #d6a77a;
  border-color: #d6a77a;
  color: white;
  border-radius: 8px;
}

.el-button--primary:hover {
  background-color: #c89466;
  border-color: #c89466;
}

.dialog-style {
  border-radius: 12px;
}

.dialog-content {
  text-align: center;
  font-size: 14px;
  color: #444;
}

.dialog-content p {
  margin: 10px 0;
}
</style>
