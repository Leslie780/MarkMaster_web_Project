<template>
  <div class="course-view">
    <div class="header">
      <h1>📚 Course Management</h1>
      <el-input
        v-model="search"
        placeholder="Search by course or lecturer"
        clearable
        size="medium"
        style="width: 300px"
      />
      <el-button type="primary" @click="goAddCourse">+ Add Course</el-button>
    </div>

    <el-row :gutter="20">
      <el-col v-for="course in filteredCourses" :key="course.id" :span="8">
        <el-card class="course-card" shadow="hover">
          <div class="card-content">
            <h3 class="course-title">{{ course.name }}</h3>
            <p class="course-detail">
              Lecturer: {{ course.lecturer }}<br />
              Credit Hours: {{ course.credits }}
            </p>
            <el-button
              size="small"
              type="success"
              @click="viewCourse(course.id)"
            >
              View
            </el-button>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();

const search = ref("");
const courses = ref([
  { id: 1, name: "Web Technology", lecturer: "Mr. Wong", credits: 3 },
  { id: 2, name: "Software Engineering", lecturer: "Dr. Tan", credits: 4 },
  { id: 3, name: "Database Systems", lecturer: "Prof. Lee", credits: 3 },
]);

const filteredCourses = computed(() => {
  if (!search.value.trim()) {
    return courses.value;
  }
  const keyword = search.value.toLowerCase();
  return courses.value.filter(
    (c) =>
      c.name.toLowerCase().includes(keyword) ||
      c.lecturer.toLowerCase().includes(keyword)
  );
});

function viewCourse(id) {
  router.push(`/courses/${id}`);
}

function goAddCourse() {
  router.push({ name: "AddCourse" });
}

// 监听路由 query 参数，有新课程时加入列表
watch(
  () => route.query.add,
  (newVal) => {
    if (newVal) {
      try {
        const newCourse = JSON.parse(newVal);
        const newId = courses.value.length
          ? courses.value[courses.value.length - 1].id + 1
          : 1;
        courses.value.push({ id: newId, ...newCourse });
        // 清空 query 避免重复添加
        router.replace({ query: {} });
      } catch (e) {
        console.error("Invalid course data in query param");
      }
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.course-view {
  background-color: #f9f6f1;
  padding: 24px;
  min-height: 100vh;
  color: #4a4a4a;
}

.header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

.course-card {
  background-color: #fff9f2;
  border-radius: 12px;
  border: 1px solid #e5ded7;
  transition: all 0.2s ease;
}

.course-card:hover {
  transform: translateY(-2px);
}

.card-content {
  padding: 12px;
}

.course-title {
  font-size: 18px;
  color: #d6a77a;
  margin-bottom: 8px;
}

.course-detail {
  font-size: 14px;
  color: #666;
  margin-bottom: 12px;
}
</style>
