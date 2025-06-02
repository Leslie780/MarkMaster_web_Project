<template>
  <div class="students-view">
    <h1 class="page-title">🎓 Students</h1>

    <!-- 搜索框 -->
    <el-input
      v-model="searchKeyword"
      placeholder="Search students by name or major"
      clearable
      class="search-input"
      prefix-icon="el-icon-search"
    />

    <!-- 学生卡片列表 -->
    <el-row :gutter="20" class="students-list" style="margin-top: 20px">
      <el-col v-for="student in pagedStudents" :key="student.id" :span="6">
        <el-card shadow="hover" class="student-card">
          <el-avatar
            :src="student.avatar"
            size="large"
            class="avatar"
            @click="openDialog(student)"
            style="cursor: pointer"
            :title="'View ' + student.name + ' details'"
          />
          <h3>{{ student.name }}</h3>
          <p>{{ student.major }}</p>
        </el-card>
      </el-col>
    </el-row>

    <!-- 分页组件 -->
    <div class="pagination-wrapper">
      <el-pagination
        background
        layout="prev, pager, next"
        :page-size="pageSize"
        :total="filteredStudents.length"
        v-model:current-page="currentPage"
      />
    </div>

    <!-- 弹窗：查看学生详情 -->
    <el-dialog
      v-model="dialogVisible"
      :title="selectedStudent?.name"
      width="400px"
      class="dialog-style"
      @close="selectedStudent = null"
    >
      <div class="dialog-content" v-if="selectedStudent">
        <el-avatar :src="selectedStudent.avatar" size="large" class="avatar" />
        <p><strong>Name:</strong> {{ selectedStudent.name }}</p>
        <p><strong>Email:</strong> {{ selectedStudent.email }}</p>
        <p><strong>Major:</strong> {{ selectedStudent.major }}</p>
        <p><strong>Bio:</strong> {{ selectedStudent.bio }}</p>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

const searchKeyword = ref("");
const dialogVisible = ref(false);
const selectedStudent = ref(null);

const currentPage = ref(1);
const pageSize = 4;

const students = ref([
  {
    id: 1,
    name: "Alice Chen",
    email: "alice.chen@student.univ.edu",
    major: "Software Engineering",
    avatar: "https://i.pravatar.cc/150?img=12",
    bio: "Passionate about software design and AI.",
  },
  {
    id: 2,
    name: "Bob Tan",
    email: "bob.tan@student.univ.edu",
    major: "Computer Science",
    avatar: "https://i.pravatar.cc/150?img=13",
    bio: "Interested in algorithms and data structures.",
  },
  {
    id: 3,
    name: "Cathy Lee",
    email: "cathy.lee@student.univ.edu",
    major: "Information Systems",
    avatar: "https://i.pravatar.cc/150?img=14",
    bio: "Enjoys working with enterprise systems.",
  },
  {
    id: 4,
    name: "David Lim",
    email: "david.lim@student.univ.edu",
    major: "Data Science",
    avatar: "https://i.pravatar.cc/150?img=15",
    bio: "Focused on predictive analytics and visualization.",
  },
  {
    id: 5,
    name: "Eva Wong",
    email: "eva.wong@student.univ.edu",
    major: "Software Engineering",
    avatar: "https://i.pravatar.cc/150?img=16",
    bio: "Loves full-stack development.",
  },
  {
    id: 6,
    name: "Frankie Ho",
    email: "frankie.ho@student.univ.edu",
    major: "Computer Science",
    avatar: "https://i.pravatar.cc/150?img=17",
    bio: "Interested in system design and networking.",
  },
]);

const filteredStudents = computed(() => {
  const kw = searchKeyword.value.toLowerCase();
  return students.value.filter(
    (s) =>
      s.name.toLowerCase().includes(kw) || s.major.toLowerCase().includes(kw)
  );
});

// 根据分页计算当前页学生列表
const pagedStudents = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredStudents.value.slice(start, start + pageSize);
});

const openDialog = (student) => {
  selectedStudent.value = student;
  dialogVisible.value = true;
};
</script>

<style scoped>
.students-view {
  padding: 24px;
  background-color: #fafafa;
}

.page-title {
  font-size: 24px;
  margin-bottom: 20px;
}

.search-input {
  max-width: 400px;
}

.student-card {
  text-align: center;
  padding: 20px;
  border-radius: 10px;
  background-color: white;
  cursor: default;
  user-select: none;
}

.student-card h3 {
  margin-top: 12px;
  margin-bottom: 6px;
  color: #333;
}

.student-card p {
  color: #666;
  font-size: 14px;
}

.avatar {
  margin-bottom: 12px;
  cursor: pointer;
  border-radius: 50%;
  transition: transform 0.2s ease;
}

.avatar:hover {
  transform: scale(1.1);
}

.pagination-wrapper {
  margin-top: 20px;
  text-align: center;
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
