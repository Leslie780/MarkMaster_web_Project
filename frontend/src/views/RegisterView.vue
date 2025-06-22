<template>
  <div class="register-container">
    <el-card class="register-card" shadow="hover">
      <div class="register-header">
        <h2>MarkMaster</h2>
      </div>

      <el-form
        :model="form"
        ref="registerForm"
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
          label="Full Name"
          prop="name"
          :rules="[
            {
              required: true,
              message: 'Please enter your name',
              trigger: 'blur',
            },
          ]"
        >
          <el-input v-model="form.name" placeholder="Enter your full name" />
        </el-form-item>

        <el-form-item
          label="Phone"
          prop="phone"
          :rules="[
            {
              required: true,
              message: 'Please enter your phone number',
              trigger: 'blur',
            },
          ]"
        >
          <el-input
            v-model="form.phone"
            placeholder="Enter your phone number"
          />
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
          <el-input v-model="form.staffNo" placeholder="Enter your Staff No." />
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
          <el-input v-model="form.email" placeholder="Enter your email" />
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
            placeholder="Enter password"
            show-password
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
            placeholder="Confirm password"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            native-type="submit"
            block
            :loading="loading"
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

<script>
import axios from "axios";
import { ElMessage } from "element-plus";

export default {
  name: "RegisterView",
  data() {
    return {
      loading: false,
      form: {
        role: "",
        email: "",
        password: "",
        confirmPassword: "",
        matricNo: "",
        staffNo: "",
        name: "",
        phone: "",
        profile_pic: "",
      },
    };
  },
  methods: {
    validateConfirmPassword(rule, value, callback) {
      if (value !== this.form.password) {
        callback(new Error("Passwords don't match"));
      } else {
        callback();
      }
    },
    async onSubmit() {
      this.$refs.registerForm.validate(async (valid) => {
        if (!valid) return;

        this.loading = true;
        try {
          const payload = {
            email: this.form.email,
            password: this.form.password,
            role: this.form.role,
            name: this.form.name,
            phone: this.form.phone,
            // profile_pic: this.form.profile_pic,
          };

          if (this.form.role === "student") {
            payload.matricNo = this.form.matricNo;
          } else {
            payload.staffNo = this.form.staffNo;
          }

          const res = await axios.post(
            "http://localhost:8085/register",
            payload
          );

          if (res.data.success) {
            ElMessage.success(res.data.message);
            this.$router.push("/login");
          } else {
            ElMessage.error(res.data.message);
          }
        } catch (error) {
          console.error("Registration failed:", {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
            fullError: error
          });
          const msg = error.response?.data?.message || "Registration failed. Server error.";
          ElMessage.error(msg);
        } finally {
          this.loading = false;
        }
      });
    },
    goLogin() {
      this.$router.push("/login");
    },
  },
};
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f4f6f9;
}

.register-card {
  width: 500px;
  padding: 30px;
}

.register-header {
  text-align: center;
  margin-bottom: 20px;
}
</style>
