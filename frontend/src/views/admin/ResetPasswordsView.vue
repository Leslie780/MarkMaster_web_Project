<template>
  <div class="forget-password-container">
    <el-card class="forget-password-card" shadow="hover">
      <div class="forget-password-header">
        <h2>Reset Password</h2>
        <p>Please enter your registered email and a new password.</p>
      </div>

      <el-form :model="form" @submit.prevent="onSubmit" label-width="120px">
        <el-form-item label="Email">
          <el-input v-model="form.email" autocomplete="off" />
        </el-form-item>

        <el-form-item label="New Password">
          <el-input
            v-model="form.new_password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
          >
            <template #suffix>
              <el-icon @click="togglePassword" class="password-toggle">
                <component :is="showPassword ? Eye : EyeClosed" />
              </el-icon>
            </template>
          </el-input>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" native-type="submit" block>
            Reset Password
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { Eye, EyeClosed } from "@element-plus/icons-vue";

const router = useRouter();

const form = reactive({
  email: "",
  new_password: "",
});

const showPassword = ref(false);

function togglePassword() {
  showPassword.value = !showPassword.value;
}

async function onSubmit() {
  if (!form.email || !form.new_password) {
    alert("Please enter both email and new password.");
    return;
  }

  try {
    const res = await axios.post("http://localhost:8085/forgot-password", {
      identifier: form.email,
      new_password: form.new_password,
    });

    if (res.data.success) {
      alert("Password updated successfully.");
      router.push("/login");
    } else {
      alert(res.data.message || "Failed to reset password.");
    }
  } catch (err) {
    alert("Server error. Please try again.");
  }
}
</script>

<style scoped>
.forget-password-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f9f6f1;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  color: #4a4a4a;
}

.forget-password-card {
  width: 400px;
  padding: 32px;
  background-color: #fff9f2;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.forget-password-header {
  text-align: center;
  margin-bottom: 20px;
}

.forget-password-header h2 {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 8px;
  color: #4a4a4a;
}

.forget-password-header p {
  font-size: 14px;
  color: #7e7e7e;
}

.el-input__inner {
  border-radius: 10px;
  border-color: #d6d0c6;
  background-color: #fcf9f5;
  font-size: 14px;
  color: #4a4a4a;
}

.el-input__inner:hover,
.el-input__inner:focus {
  border-color: #d6a77a;
}

.el-button--primary {
  background-color: #e7eaf0;
  color: #3a3a3a;
  font-weight: 600;
  border-radius: 10px;
  border: none;
  transition: background-color 0.3s ease;
}

.el-button--primary:hover {
  background-color: #cfd8e3;
  color: #1a2533;
}

.el-button--text {
  color: #7e7e7e;
  font-weight: 500;
}

.el-button--text:hover {
  color: #d6a77a;
  text-decoration: underline;
}

.back-to-login {
  margin-top: 16px;
  text-align: center;
}

/* Scrollbar style */
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
/* 给密码切换的眼睛图标单独设色 */
.el-icon.password-toggle {
  cursor: pointer;
  color: #606266; /* Element Plus 默认 icon 灰色 */
  transition: color 0.2s;
}

.el-icon.password-toggle:hover {
  color: #d6a77a; /* 你整体的主题色 */
}
</style>
