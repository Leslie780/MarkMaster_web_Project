<template>
  <div class="course-view">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h2 class="page-title">
            <i class="title-icon el-icon-document-add"></i>
            {{ isEdit ? "Edit Course" : "Add New Course" }}
          </h2>
          <p class="page-subtitle">
            {{
              isEdit
                ? "Update course information"
                : "Create a new course in the system"
            }}
          </p>
        </div>
      </div>
    </div>

    <!-- Form Card -->
    <el-card class="form-card" shadow="never">
      <el-form
        :model="form"
        :rules="rules"
        ref="formRef"
        label-width="140px"
        status-icon
        class="course-form"
      >
        <!-- Basic Information Section -->
        <div class="form-section">
          <h3 class="section-title">
            <i class="section-icon el-icon-info"></i>
            Basic Information
          </h3>
          <div class="form-grid">
            <el-form-item
              label="Course Name"
              prop="course_name"
              class="form-item"
            >
              <el-input
                v-model="form.course_name"
                placeholder="Enter course name"
              />
            </el-form-item>

            <el-form-item
              label="Course Code"
              prop="course_code"
              class="form-item"
            >
              <el-input
                v-model="form.course_code"
                placeholder="e.g., COMP1234"
              />
            </el-form-item>

            <el-form-item label="Lecturer" prop="lecturer_id" class="form-item">
              <el-select
                v-model="form.lecturer_id"
                placeholder="Select Lecturer"
                class="full-width"
              >
                <el-option
                  v-for="lecturer in lecturers"
                  :key="lecturer.id"
                  :label="lecturer.name"
                  :value="lecturer.id"
                />
              </el-select>
            </el-form-item>

            <el-form-item
              label="Credit Hours"
              prop="credit_hours"
              class="form-item"
            >
              <el-select
                v-model.number="form.credit_hours"
                placeholder="Select Credit Hours"
                class="full-width"
              >
                <el-option label="1 Credit" :value="1" />
                <el-option label="2 Credits" :value="2" />
                <el-option label="3 Credits" :value="3" />
                <el-option label="4 Credits" :value="4" />
                <el-option label="5 Credits" :value="5" />
                <el-option label="6 Credits" :value="6" />
                <el-option label="7 Credits" :value="7" />
                <el-option label="8 Credits" :value="8" />
              </el-select>
            </el-form-item>
          </div>
        </div>

        <!-- Academic Details Section -->
        <div class="form-section">
          <h3 class="section-title">
            <i class="section-icon el-icon-calendar"></i>
            Academic Details
          </h3>
          <div class="form-grid">
            <el-form-item
              label="Academic Year"
              prop="academic_year"
              class="form-item"
            >
              <el-select
                v-model="form.academic_year"
                placeholder="Select Academic Year"
                class="full-width"
              >
                <el-option
                  v-for="year in academicYears"
                  :key="year"
                  :label="year"
                  :value="year"
                />
              </el-select>
            </el-form-item>

            <el-form-item label="Semester" prop="semester" class="form-item">
              <el-select
                v-model="form.semester"
                placeholder="Select Semester"
                class="full-width"
              >
                <el-option label="Semester 1" value="1" />
                <el-option label="Semester 2" value="2" />
              </el-select>
            </el-form-item>

            <el-form-item label="Status" class="form-item">
              <el-select v-model="form.status" class="full-width">
                <el-option label="Active" value="active" />
                <el-option label="Inactive" value="inactive" />
              </el-select>
            </el-form-item>
          </div>
        </div>

        <!-- Description Section -->
        <div class="form-section">
          <h3 class="section-title">
            <i class="section-icon el-icon-document"></i>
            Course Description
          </h3>
          <el-form-item label="Description" class="form-item description-item">
            <el-input
              v-model="form.description"
              type="textarea"
              :rows="4"
              placeholder="Enter course description, objectives, and key topics..."
            />
          </el-form-item>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions">
          <el-button
            type="primary"
            size="large"
            :loading="loading"
            :disabled="loading"
            @click="submit"
            class="submit-button"
          >
            <i class="el-icon-check"></i>
            {{ isEdit ? "Update Course" : "Create Course" }}
          </el-button>
          <el-button size="large" @click="$router.back()" class="cancel-button">
            <i class="el-icon-close"></i>
            Cancel
          </el-button>
        </div>
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
      validator: (_rule, value, callback) => {
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
      validator: (_rule, value, callback) => {
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
  requestAnimationFrame(() => {
    document.title = isEdit.value ? "Edit Course" : "Add New Course";
  });
  await fetchLecturers();
  await fetchCourse();
});
</script>

<style scoped>
.course-view {
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

/* 表单卡片 */
.form-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: none;
  overflow: hidden;
}

.course-form {
  padding: 32px;
}

/* 表单分组 */
.form-section {
  margin-bottom: 40px;
  padding-bottom: 32px;
  border-bottom: 1px solid #f0f2f5;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 24px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-icon {
  color: #409eff;
  font-size: 20px;
}

/* 表单网格布局 */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.assessment-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 16px;
}

/* 表单项样式 */
.form-item {
  margin-bottom: 0;
}

.form-item :deep(.el-form-item__label) {
  font-weight: 500;
  color: #2c3e50;
  line-height: 1.6;
}

.form-item :deep(.el-input__inner),
.form-item :deep(.el-textarea__inner) {
  border-radius: 8px;
  border: 1px solid #e4e7ed;
  transition: all 0.3s ease;
  font-size: 14px;
}

.form-item :deep(.el-input__inner:focus),
.form-item :deep(.el-textarea__inner:focus) {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

.form-item :deep(.el-select) {
  width: 100%;
}

.full-width {
  width: 100%;
}

.percentage-input :deep(.el-input-group__append) {
  background: #f5f7fa;
  border-color: #e4e7ed;
  color: #909399;
  font-weight: 500;
}

/* 描述项特殊样式 */
.description-item {
  grid-column: 1 / -1;
}

.description-item :deep(.el-textarea__inner) {
  resize: vertical;
  min-height: 100px;
}

/* 评估配置提示 */
.assessment-note {
  background: #f0f9ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  padding: 12px 16px;
  color: #1e40af;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.assessment-note i {
  color: #3b82f6;
}

/* 操作按钮 */
.form-actions {
  margin-top: 40px;
  padding-top: 32px;
  border-top: 1px solid #f0f2f5;
  display: flex;
  gap: 16px;
  justify-content: flex-end;
}

.submit-button {
  height: 48px;
  padding: 0 32px;
  border-radius: 12px;
  font-weight: 500;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

.submit-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.cancel-button {
  height: 48px;
  padding: 0 24px;
  border-radius: 12px;
  font-weight: 500;
  color: #7f8c8d;
  border-color: #ddd;
  transition: all 0.3s ease;
}

.cancel-button:hover {
  color: #2c3e50;
  border-color: #409eff;
  background: #f8f9fa;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .course-view {
    padding: 16px;
  }

  .header-content {
    padding: 24px;
  }

  .page-title {
    font-size: 24px;
  }

  .course-form {
    padding: 24px;
  }

  .form-grid,
  .assessment-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .form-actions {
    flex-direction: column;
  }

  .submit-button,
  .cancel-button {
    width: 100%;
  }
}

/* 验证错误样式优化 */
.form-item :deep(.el-form-item__error) {
  background: #fef2f2;
  color: #dc2626;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  margin-top: 4px;
  border: 1px solid #fecaca;
}

.form-item :deep(.is-error .el-input__inner),
.form-item :deep(.is-error .el-textarea__inner) {
  border-color: #f56565;
  box-shadow: 0 0 0 2px rgba(245, 101, 101, 0.1);
}

/* 选择器选项样式 */
:deep(.el-select-dropdown__item) {
  padding: 12px 16px;
  font-size: 14px;
}

:deep(.el-select-dropdown__item:hover) {
  background: #f0f9ff;
}

:deep(.el-select-dropdown__item.selected) {
  background: #409eff;
  color: white;
  font-weight: 500;
}

/* 加载状态 */
.submit-button.is-loading {
  pointer-events: none;
  opacity: 0.7;
}
</style>
