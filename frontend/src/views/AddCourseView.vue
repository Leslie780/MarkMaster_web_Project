<template>
  <el-card class="add-course-card">
    <h2>Add New Course</h2>
    <el-form :model="form" :rules="rules" ref="courseForm" label-width="120px">
      <el-form-item label="Course Name" prop="name">
        <el-input v-model="form.name" autocomplete="off" />
      </el-form-item>

      <el-form-item label="Lecturer" prop="lecturer">
        <el-input v-model="form.lecturer" autocomplete="off" />
      </el-form-item>

      <el-form-item label="Credit Hours" prop="credits">
        <el-input-number v-model="form.credits" :min="1" :max="10" />
      </el-form-item>

      <el-form-item>
        <el-button @click="goBack">Cancel</el-button>
        <el-button type="primary" @click="submitForm">Add</el-button>
      </el-form-item>
    </el-form>
  </el-card>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { ElMessage } from "element-plus";

const router = useRouter();

const form = ref({
  name: "",
  lecturer: "",
  credits: 3,
});

const rules = {
  name: [
    { required: true, message: "Please input course name", trigger: "blur" },
  ],
  lecturer: [
    { required: true, message: "Please input lecturer name", trigger: "blur" },
  ],
  credits: [
    {
      type: "number",
      required: true,
      message: "Please input credits",
      trigger: "change",
    },
  ],
};

const courseForm = ref(null);

function submitForm() {
  courseForm.value.validate((valid) => {
    if (valid) {
      // 发送添加课程事件给父页面或使用全局状态管理
      router.push({
        name: "Courses",
        query: { add: JSON.stringify(form.value) },
      });
      ElMessage.success("Course added successfully!");
    } else {
      ElMessage.error("Please fix the errors in the form.");
      return false;
    }
  });
}

function goBack() {
  router.back();
}
</script>

<style scoped>
.add-course-card {
  max-width: 480px;
  margin: 40px auto;
  padding: 20px;
  background-color: #fff9f2;
  border-radius: 12px;
}
</style>
