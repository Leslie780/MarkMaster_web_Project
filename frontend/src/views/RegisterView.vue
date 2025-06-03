<template>
  <div class="register-container">
    <el-card class="register-card" shadow="hover">
      <div class="register-header">
        <h2>MarkMasterr</h2>
      </div>

      <el-form
        :model="form"
        @submit.prevent="onSubmit"
        label-width="120px"
        ref="registerForm"
      >
        <el-form-item
          label="Role"
          prop="role"
          :rules="[
            {
              required: true,
              message: 'Please select your role',
              trigger: 'change',
            },
          ]"
        >
          <el-select v-model="form.role" placeholder="Select Role">
            <el-option label="Student" value="student" />
            <el-option label="Lecturer" value="lecturer" />
            <el-option label="Academic Advisor" value="academicAdvisor" />
            <el-option label="Admin" value="admin" />
          </el-select>
        </el-form-item>

        <el-form-item
          v-if="form.role === 'student'"
          label="Matric No."
          prop="matricNo"
          :rules="[
            {
              required: true,
              message: 'Please input your matric no.',
              trigger: 'blur',
            },
          ]"
        >
          <el-input
            v-model="form.matricNo"
            autocomplete="off"
            placeholder="Enter your Matric No."
          />
        </el-form-item>

        <el-form-item
          v-if="['lecturer', 'admin', 'academicAdvisor'].includes(form.role)"
          label="Staff No."
          prop="staffNo"
          :rules="[
            {
              required: true,
              message: 'Please input your staff no.',
              trigger: 'blur',
            },
          ]"
        >
          <el-input
            v-model="form.staffNo"
            autocomplete="off"
            placeholder="Enter your Staff No."
          />
        </el-form-item>

        <el-form-item
          label="Email"
          prop="email"
          :rules="[
            {
              required: true,
              message: 'Please input your email',
              trigger: 'blur',
            },
            {
              type: 'email',
              message: 'Please enter a valid email',
              trigger: 'blur',
            },
          ]"
        >
          <el-input
            v-model="form.email"
            autocomplete="off"
            placeholder="Enter your email"
          />
        </el-form-item>

        <el-form-item
          label="Password"
          prop="password"
          :rules="[
            {
              required: true,
              message: 'Please input your password',
              trigger: 'blur',
            },
          ]"
        >
          <el-input
            v-model="form.password"
            type="password"
            autocomplete="off"
            show-password
            placeholder="Enter password"
          />
        </el-form-item>

        <el-form-item
          label="Confirm Password"
          prop="confirmPassword"
          :rules="[
            {
              required: true,
              message: 'Please confirm your password',
              trigger: 'blur',
            },
            { validator: validateConfirmPassword, trigger: 'blur' },
          ]"
        >
          <el-input
            v-model="form.confirmPassword"
            type="password"
            autocomplete="off"
            show-password
            placeholder="Confirm your password"
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" native-type="submit" block
            >Register</el-button
          >
        </el-form-item>

        <div style="text-align: right; margin-bottom: 1rem">
          <el-button type="text" @click="goLogin"
            >Already have an account? Login</el-button
          >
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const registerForm = ref(null);

const form = reactive({
  role: "",
  matricNo: "",
  staffNo: "",
  email: "",
  password: "",
  confirmPassword: "",
});

// 自定义密码确认校验
function validateConfirmPassword(rule, value, callback) {
  if (value !== form.password) {
    callback(new Error("Passwords do not match"));
  } else {
    callback();
  }
}

function onSubmit() {
  registerForm.value.validate((valid) => {
    if (valid) {
      // 模拟注册成功
      alert("Registration successful!");
      router.push("/login");
    } else {
      alert("Please correct the errors in the form.");
      return false;
    }
  });
}

function goLogin() {
  router.push("/login");
}
</script>

<style scoped>
.register-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f9f6f1;
  color: #3a3a3a;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  font-size: 16px;
  user-select: none;
}

.register-card {
  width: 360px;
  padding: 32px;
  border-radius: 16px;
  background-color: #fff9f2;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.register-header {
  margin-bottom: 32px;
  text-align: center;
}

.register-header h2 {
  font-weight: 700;
  font-size: 30px;
  color: #3a3a3a;
  letter-spacing: 1px;
}

.el-form-item__label {
  color: #7e7e7e;
  font-weight: 600;
  font-size: 14px;
}

.el-input__inner {
  border-radius: 10px;
  border-color: #d6d0c6;
  background-color: #fcf9f5;
  font-size: 14px;
  color: #3a3a3a;
  transition: border-color 0.3s ease;
}

.el-input__inner:focus,
.el-input__inner:hover {
  border-color: #d6a77a;
}

.el-button--primary {
  background-color: #e7eaf0;
  color: #3a3a3a;
  font-weight: 600;
  border-radius: 10px;
  border: none;
  transition: background-color 0.3s ease;
  box-shadow: none;
}

.el-button--primary:hover {
  background-color: #cfd8e3;
  color: #1a2533;
  box-shadow: none;
}

.el-button--text {
  color: #7e7e7e;
  font-weight: 500;
}

.el-button--text:hover {
  color: #d6a77a;
  background-color: transparent;
  text-decoration: underline;
}

::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

::-webkit-scrollbar-track {
  background: #f9f6f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background-color: #c1b7a6;
  border-radius: 10px;
  border: 2px solid #f9f6f1;
}

::-webkit-scrollbar-thumb:hover {
  background-color: #a58e70;
}

* {
  scrollbar-width: thin;
  scrollbar-color: #c1b7a6 #f9f6f1;
}
</style>
