<template>
  <div class="course-view">
    <el-card shadow="hover">
      <h2 style="margin-bottom: 20px">{{ isEdit ? "Edit" : "Add" }} Course</h2>

      <el-form
        :model="form"
        :rules="rules"
        ref="formRef"
        label-width="140px"
        status-icon
      >
        <el-form-item label="Course Name" prop="course_name">
          <el-input v-model="form.course_name" />
        </el-form-item>

        <el-form-item label="Course Code" prop="course_code">
          <el-input v-model="form.course_code" />
        </el-form-item>

        <el-form-item label="Lecturer" prop="lecturer_id">
          <el-select v-model="form.lecturer_id" placeholder="Select Lecturer">
            <el-option
              v-for="lecturer in lecturers"
              :key="lecturer.id"
              :label="lecturer.name"
              :value="lecturer.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Academic Year" prop="academic_year">
          <el-select v-model="form.academic_year" placeholder="Select Year">
            <el-option
              v-for="year in academicYears"
              :key="year"
              :label="year"
              :value="year"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Semester" prop="semester">
          <el-select v-model="form.semester" placeholder="Select Semester">
            <el-option label="1" value="1" />
            <el-option label="2" value="2" />
          </el-select>
        </el-form-item>

        <el-form-item label="Credit Hours" prop="credit_hours">
          <el-select
            v-model.number="form.credit_hours"
            placeholder="Select Credit"
          >
            <el-option label="1" :value="1" />
            <el-option label="2" :value="2" />
            <el-option label="3" :value="3" />
            <el-option label="4" :value="4" />
            <el-option label="5" :value="5" />
            <el-option label="6" :value="6" />
            <el-option label="7" :value="7" />
            <el-option label="8" :value="8" />
          </el-select>
        </el-form-item>

        <el-form-item label="Description">
          <el-input v-model="form.description" type="textarea" />
        </el-form-item>

        <el-form-item label="Final Exam %" prop="final_exam_percentage">
          <el-input v-model.number="form.final_exam_percentage" type="number" />
        </el-form-item>

        <el-form-item
          label="Continuous Assessment %"
          prop="continuous_assessment_percentage"
        >
          <el-input
            v-model.number="form.continuous_assessment_percentage"
            type="number"
          />
        </el-form-item>

        <el-form-item label="Status">
          <el-select v-model="form.status">
            <el-option label="Active" value="active" />
            <el-option label="Inactive" value="inactive" />
          </el-select>
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            :loading="loading"
            :disabled="loading"
            @click="submit"
          >
            {{ isEdit ? "Update" : "Create" }}
          </el-button>
          <el-button @click="$router.back()">Cancel</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { ElMessage } from "element-plus";

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const formRef = ref();

const academicYears = Array.from({ length: 5 }, (_, i) => {
  const start = 2024 + i;
  return `${start}/${start + 1}`;
});

const form = ref({
  course_name: "",
  course_code: "",
  lecturer_id: "",
  academic_year: "",
  semester: "",
  credit_hours: null,
  description: "",
  final_exam_percentage: 0,
  continuous_assessment_percentage: 0,
  status: "active",
});

const lecturers = ref([]);

const rules = {
  course_name: [
    { required: true, message: "Course name is required", trigger: "blur" },
  ],
  course_code: [
    { required: true, message: "Course code is required", trigger: "blur" },
    {
      pattern: /^[A-Z]{4}[0-9]{4}$/,
      message: "Format must be like ABCD1234",
      trigger: "blur",
    },
  ],
  lecturer_id: [
    { required: true, message: "Please select a lecturer", trigger: "change" },
  ],
  academic_year: [
    {
      required: true,
      message: "Please select academic year",
      trigger: "change",
    },
  ],
  semester: [
    { required: true, message: "Please select semester", trigger: "change" },
  ],
  credit_hours: [
    {
      required: true,
      message: "Please select credit hours",
      trigger: "change",
    },
  ],
  final_exam_percentage: [
    {
      validator: (rule, value, callback) => {
        if (value < 30) {
          callback(new Error("Final Exam must be at least 30%"));
        } else {
          callback();
        }
      },
      trigger: "blur",
    },
  ],
  continuous_assessment_percentage: [
    {
      validator: (rule, value, callback) => {
        const total = value + form.value.final_exam_percentage;
        if (total !== 100) {
          callback(new Error("Final + Continuous must equal 100%"));
        } else {
          callback();
        }
      },
      trigger: "blur",
    },
  ],
};

const fetchLecturers = async () => {
  try {
    const res = await axios.get(
      "http://localhost:8085/user-management?role=lecturer"
    );
    if (res.data.success) {
      lecturers.value = res.data.users;
    } else {
      ElMessage.error("Failed to fetch lecturers");
    }
  } catch (e) {
    ElMessage.error("Lecturer fetch error");
  }
};

const fetchCourse = async () => {
  if (isEdit.value) {
    try {
      const res = await axios.get(
        `http://localhost:8085/course-management?id=${route.params.id}`
      );
      if (res.data.success) {
        Object.assign(form.value, res.data.data);
      } else {
        ElMessage.error("Failed to fetch course data");
      }
    } catch (e) {
      ElMessage.error("Course fetch error");
    }
  }
};

const submit = async () => {
  await formRef.value.validate(async (valid) => {
    if (!valid) return;

    loading.value = true;
    try {
      if (isEdit.value) {
        await axios.put("http://localhost:8085/course-management", {
          ...form.value,
          id: route.params.id,
        });
        ElMessage.success("Course updated successfully");
      } else {
        await axios.post("http://localhost:8085/course-management", form.value);
        ElMessage.success("Course created successfully");
      }
      router.push({ name: "Courses" });
    } catch (e) {
      ElMessage.error("Submit failed. Please try again.");
    } finally {
      loading.value = false;
    }
  });
};

onMounted(async () => {
  await fetchLecturers();
  await fetchCourse();
});
</script>

<style scoped>
.course-view {
  background-color: #f9f6f1;
  padding: 32px;
  min-height: 100vh;
  color: #4a4a4a;
}
</style>
