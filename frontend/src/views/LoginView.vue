<template>
  <div class="login-container">
    <el-card class="login-card" shadow="hover">
      <div class="login-header">
        <h2>MarkMaster</h2>
      </div>
      <el-form :model="form" @submit.prevent="onSubmit" label-width="100px">
        <el-form-item label="Email">
          <el-input v-model="form.email" autocomplete="off" />
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
      </el-form>

      <div class="register-link">
        <span>Don't have an account?</span>
        <el-button type="text" @click="goRegister">Register</el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { reactive } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const form = reactive({
  email: "",
  password: "",
});

function onSubmit() {
  if (form.email && form.password) {
    localStorage.setItem("token", "fake-token");
    router.push("/");
  } else {
    alert("Please enter email and password");
  }
}

function goRegister() {
  router.push("/register");
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
  margin-top: 20px;
  text-align: center;
  font-size: 15px;
  color: #7e7e7e;
  user-select: none;
}

.register-link span {
  margin-right: 6px;
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
