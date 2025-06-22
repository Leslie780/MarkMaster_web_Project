<template>
  <div class="login-container">
    <el-card class="login-card" shadow="hover">
      <div class="login-header">
        <h2>Login - MarkMaster</h2>
      </div>

      <el-form
        :model="form"
        ref="loginFormRef"
        label-width="120px"
        @submit.prevent="onSubmit"
      >
        <el-form-item label="Role" prop="role">
          <el-select v-model="form.role" placeholder="Select Role">
            <el-option label="Student" value="student" />
            <el-option label="Lecturer" value="lecturer" />
            <el-option label="Academic Advisor" value="academicAdvisor" />
            <el-option label="Admin" value="admin" />
          </el-select>
        </el-form-item>

        <el-form-item label="Identifier" prop="identifier">
          <el-input
            v-model="form.identifier"
            placeholder="Enter Email / Matric No. / Staff No."
            autocomplete="off"
          />
        </el-form-item>

        <el-form-item label="Password" prop="password">
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
            :loading="loading"
            @click="handleLogin"
            style="width: 100%"
          >
            Login
          </el-button>
        </el-form-item>

        <div style="text-align: right; margin-bottom: 1rem">
          <el-button type="text" @click="goRegister">
            Don't have an account? Register
          </el-button>
          <br />
          <el-button type="text" @click="goForgetPassword">
            Forgot Password?
          </el-button>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { ElMessage } from 'element-plus';

const router = useRouter();
const loading = ref(false);
const loginFormRef = ref(null);

const form = reactive({
  role: "",
  identifier: "",
  password: "",
});

// Simple validation function
function validateForm() {
  if (!form.role) {
    ElMessage.error('Please select your role');
    return false;
  }
  
  if (!form.identifier) {
    ElMessage.error('Please enter your identifier');
    return false;
  }
  
  if (!form.password) {
    ElMessage.error('Please enter your password');
    return false;
  }
  
  return true;
}

async function handleLogin() {
  // Simple validation
  if (!validateForm()) {
    return;
  }

  loading.value = true;
  
  try {
    console.log('Attempting login with:', {
      identifier: form.identifier,
      role: form.role
    });

    const response = await fetch("http://localhost:8085/login.php", {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({
        identifier: form.identifier.trim(),
        password: form.password,
        role: form.role
      }),
    });

    console.log('Response status:', response.status);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log('Response data:', data);

    if (data.success) {
      // Store user data
      localStorage.setItem("user", JSON.stringify(data.user));
      ElMessage.success('Login successful!');
      
      // Navigate to dashboard
      router.push("/dashboard");
    } else {
      ElMessage.error(data.message || "Login failed");
    }
    
  } catch (error) {
    console.error('Login error:', error);
    ElMessage.error("Login failed: " + error.message);
  } finally {
    loading.value = false;
  }
}

function onSubmit() {
  handleLogin();
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

.el-form-item {
  margin-bottom: 20px;
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
</style>