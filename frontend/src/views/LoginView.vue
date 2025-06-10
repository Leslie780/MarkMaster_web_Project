<template>
  <div class="login-container">
    <el-card class="login-card" shadow="hover">
      <div class="login-header">
        <h2>Login - MarkMaster</h2>
      </div>

      <el-form
        :model="form"
        ref="loginForm"
        label-width="120px"
        @submit.prevent="onSubmit"
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
              message: 'Please input your Matric No.',
              trigger: 'blur',
            },
          ]"
        >
          <el-input
            v-model="form.matricNo"
            placeholder="Enter your Matric No."
            autocomplete="off"
          />
        </el-form-item>

        <el-form-item
          v-if="['lecturer', 'admin', 'academicAdvisor'].includes(form.role)"
          label="Staff No."
          prop="staffNo"
          :rules="[
            {
              required: true,
              message: 'Please input your Staff No.',
              trigger: 'blur',
            },
          ]"
        >
          <el-input
            v-model="form.staffNo"
            placeholder="Enter your Staff No."
            autocomplete="off"
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
            placeholder="Enter your password"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            native-type="submit"
            :loading="loading"
            block
            >Login</el-button
          >
        </el-form-item>

        <div style="text-align: right; margin-bottom: 1rem">
          <el-button type="text" @click="goRegister"
            >Don't have an account? Register</el-button
          >
          <el-button type="text" @click="goForgetPassword"
            >Forgot Password?</el-button
          >
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const loading = ref(false);

const form = reactive({
  role: "",
  matricNo: "",
  staffNo: "",
  password: "",
});

watch(
  () => form.role,
  (newRole) => {
    if (newRole !== "student") form.matricNo = "";
    if (!["lecturer", "admin", "academicAdvisor"].includes(newRole))
      form.staffNo = "";
  }
);

async function onSubmit() {
  let identifier = "";
  if (form.role === "student") identifier = form.matricNo;
  else if (["lecturer", "admin", "academicAdvisor"].includes(form.role))
    identifier = form.staffNo;

  if (!identifier || !form.password) {
    alert("Please fill in all required fields.");
    return;
  }

  loading.value = true;
  try {
    const response = await fetch("http://localhost:8085/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        identifier,
        password: form.password,
      }),
    });

    const data = await response.json();
    if (data.success) {
      localStorage.setItem("user", JSON.stringify(data.user));
      router.push("/");
    } else {
      alert(data.message || "Login failed");
    }
  } catch (error) {
    alert("Error: " + error.message);
  } finally {
    loading.value = false;
  }
}

function goRegister() {
  router.push("/register");
}

function goForgetPassword() {
  router.push("/forget-password");
}
</script>

<style scoped>
.login-container {
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

.login-card {
  width: 360px;
  padding: 32px;
  border-radius: 16px;
  background-color: #fff9f2;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.login-header {
  margin-bottom: 32px;
  text-align: center;
}

.login-header h2 {
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

.register-link {
  font-size: 15px;
  color: #7e7e7e;
  display: flex;
  align-items: center;
  user-select: none;
}

.register-link span {
  margin-right: 6px;
}

.register-link .el-button--text {
  color: #7e7e7e;
  font-weight: 500;
}

.register-link .el-button--text:hover {
  color: #d6a77a;
  background-color: transparent;
  text-decoration: underline;
}

.links-block {
  margin-top: 1rem;
  user-select: none;
}

.forgot-link {
  display: inline-block;
  margin-top: 6px;
  margin-left: 26px; /* 左缩进，和 Register 按钮左对齐 */
  color: #7e7e7e;
  font-weight: 500;
  padding: 0;
}

.forgot-link:hover {
  color: #d6a77a;
  background-color: transparent;
  text-decoration: underline;
}

/* 滚动条样式 */
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
