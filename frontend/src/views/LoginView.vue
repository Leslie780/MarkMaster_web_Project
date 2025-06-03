<template>
  <div class="login-container">
    <el-card class="login-card" shadow="hover">
      <div class="login-header">
        <h2>MarkMaster</h2>
      </div>

      <el-form :model="form" @submit.prevent="onSubmit" label-width="120px">
        <el-form-item label="Role">
          <el-select v-model="form.role" placeholder="Select Role">
            <el-option label="Student" value="student" />
            <el-option label="Lecturer" value="lecturer" />
            <el-option label="Academic Advisor" value="academicAdvisor" />
            <el-option label="Admin" value="admin" />
          </el-select>
        </el-form-item>

        <el-form-item v-if="form.role === 'student'" label="Matric No.">
          <el-input v-model="form.matricNo" autocomplete="off" />
        </el-form-item>

        <el-form-item
          v-if="['lecturer', 'admin', 'academicAdvisor'].includes(form.role)"
          label="Staff No."
        >
          <el-input v-model="form.staffNo" autocomplete="off" />
        </el-form-item>

        <el-form-item label="Password">
          <el-input
            v-model="form.password"
            type="password"
            autocomplete="off"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" native-type="submit" block>Login</el-button>
        </el-form-item>

        <!-- 两个链接分两行 -->
        <div class="links-block">
          <div class="register-link">
            <span>Don't have an account?</span>
            <el-button type="text" @click="goRegister">Register</el-button>
          </div>

          <el-button type="text" class="forgot-link" @click="goForgetPassword">
            Forgot Password?
          </el-button>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { reactive, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const form = reactive({
  role: "",
  matricNo: "",
  staffNo: "",
  password: "",
});

watch(
  () => form.role,
  (newRole) => {
    if (newRole !== "student") {
      form.matricNo = "";
    }
    if (!["lecturer", "admin", "academicAdvisor"].includes(newRole)) {
      form.staffNo = "";
    }
  }
);

function onSubmit() {
  if (
    form.role &&
    form.password &&
    ((form.role === "student" && form.matricNo) ||
      (["lecturer", "admin", "academicAdvisor"].includes(form.role) &&
        form.staffNo))
  ) {
    localStorage.setItem("token", "fake-token");
    router.push("/");
  } else {
    alert("Please fill in all required fields.");
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
