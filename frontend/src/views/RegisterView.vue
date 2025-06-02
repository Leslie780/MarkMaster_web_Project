<template>
  <div class="register-container">
    <header class="header">MarkMaster</header>

    <div class="main-content">
      <el-menu
        class="el-menu-demo"
        default-active="student"
        router
        style="width: 180px; min-height: 320px"
      >
        <el-menu-item index="student">Register</el-menu-item>
      </el-menu>

      <el-card class="register-card" shadow="hover">
        <div class="register-header">
          <h2>Create Account</h2>
        </div>
        <el-form
          :model="form"
          :rules="rules"
          ref="registerForm"
          label-width="120px"
          @submit.prevent="onSubmit"
        >
          <el-form-item label="Email" prop="email">
            <el-input v-model="form.email" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Username" prop="username">
            <el-input v-model="form.username" autocomplete="off" />
          </el-form-item>

          <el-form-item label="Password" prop="password">
            <el-input
              v-model="form.password"
              type="password"
              autocomplete="off"
              show-password
            />
          </el-form-item>

          <el-form-item label="Confirm Password" prop="confirmPassword">
            <el-input
              v-model="form.confirmPassword"
              type="password"
              autocomplete="off"
              show-password
            />
          </el-form-item>

          <el-form-item label="Role" prop="role">
            <el-select v-model="form.role" placeholder="Select role">
              <el-option label="Student" value="student"></el-option>
              <el-option label="Lecturer" value="lecturer"></el-option>
              <el-option
                label="Academic Advisor"
                value="academicAdvisor"
              ></el-option>
              <el-option label="Admin" value="admin"></el-option>
            </el-select>
          </el-form-item>

          <el-form-item>
            <el-button type="primary" native-type="submit" block
              >Register</el-button
            >
          </el-form-item>
        </el-form>

        <div class="login-link">
          <span>Already have an account?</span>
          <el-button type="text" @click="goLogin">Login</el-button>
        </div>
      </el-card>
    </div>

    <footer class="footer">© 2025 Nordic Style App</footer>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const registerForm = ref(null);

const form = reactive({
  username: "",
  password: "",
  confirmPassword: "",
  role: "",
});

const rules = {
  email: [
    { required: true, message: "Please input email", trigger: "blur" },
    {
      type: "email",
      message: "Please enter a valid email address",
      trigger: ["blur", "change"],
    },
  ],

  username: [
    { required: true, message: "Please input username", trigger: "blur" },
    {
      min: 3,
      message: "Username must be at least 3 characters",
      trigger: "blur",
    },
  ],
  password: [
    { required: true, message: "Please input password", trigger: "blur" },
    {
      min: 6,
      message: "Password must be at least 6 characters",
      trigger: "blur",
    },
  ],
  confirmPassword: [
    { required: true, message: "Please confirm password", trigger: "blur" },
    {
      validator: (rule, value, callback) => {
        if (value !== form.password) {
          callback(new Error("Passwords do not match"));
        } else {
          callback();
        }
      },
      trigger: "blur",
    },
  ],
  role: [
    { required: true, message: "Please select a role", trigger: "change" },
  ],
};

function onSubmit() {
  registerForm.value.validate((valid) => {
    if (!valid) return;

    const users = JSON.parse(localStorage.getItem("users") || "[]");
    if (users.find((u) => u.username === form.username)) {
      alert("Username already exists");
      return;
    }

    users.push({
      username: form.username,
      password: form.password,
      role: form.role,
    });

    localStorage.setItem("users", JSON.stringify(users));
    alert("Registration successful! Please login.");
    router.push("/login");
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
  flex-direction: column;
  background-color: #f9f6f1;
  color: #3a3a3a;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  font-size: 16px;
  user-select: none;
}

.header,
.footer {
  height: 60px;
  background-color: #e9e6df;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #4a4a4a;
  font-weight: 600;
  box-shadow: inset 0 -1px 3px rgb(214 167 122 / 0.15);
  flex-shrink: 0;
}

.main-content {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
  gap: 24px;
  overflow: auto;
}

.el-menu-demo {
  background-color: transparent;
  color: #555;
  font-weight: 600;
  font-size: 15px;
  border: none;
  box-shadow: none;
  min-width: 180px;
}

.el-menu-demo .el-menu-item.is-active,
.el-menu-demo .el-submenu.is-active > .el-submenu__title {
  background-color: #d6a77a;
  color: #3a3a3a;
  border-radius: 8px;
}

.register-card {
  width: 420px;
  padding: 32px;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  background-color: #fff9f2;
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

.el-select .el-input__inner {
  border-radius: 10px;
  background-color: #fcf9f5;
  border-color: #d6d0c6;
}

.el-select .el-input__inner:hover,
.el-select .el-input__inner:focus {
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

.login-link {
  margin-top: 20px;
  text-align: center;
  font-size: 15px;
  color: #7e7e7e;
  user-select: none;
}

.login-link span {
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
