<template>
  <div class="register-container">
    <el-card class="register-card" shadow="hover">
      <div class="register-header">
        <h2>MarkMaster</h2>
        <p class="sub-title">Create your account</p>
      </div>

      <el-form
        :model="form"
        ref="registerForm"
        label-width="130px"
        @submit.prevent="onSubmit"
      >
        <!-- Role -->
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
            <el-option label="Academic Advisor" value="academic advisor" />
            <el-option label="Admin" value="admin" />
          </el-select>
        </el-form-item>

        <!-- Name -->
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

        <!-- Phone -->
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

        <!-- Matric No -->
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

        <!-- Staff No -->
        <el-form-item
          v-if="['lecturer', 'admin', 'academic advisor'].includes(form.role)"
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

        <!-- Email -->
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

        <!-- Password -->
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
            show-password
            placeholder="Enter password"
          />
        </el-form-item>

        <!-- Confirm Password -->
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
            show-password
            placeholder="Confirm password"
          />
        </el-form-item>

        <!-- Upload Avatar -->
        <el-form-item label="Profile Picture">
          <el-upload
            class="upload-demo"
            :auto-upload="false"
            :show-file-list="false"
            :on-change="onAvatarChange"
            accept="image/*"
          >
            <el-button type="primary">Upload Avatar</el-button>
          </el-upload>
          <div v-if="form.profile_pic" class="avatar-preview">
            <img :src="form.profile_pic" />
          </div>
        </el-form-item>

        <!-- Submit -->
        <el-form-item>
          <el-button
            type="primary"
            native-type="submit"
            block
            :loading="loading"
            :disabled="loading"
            >Register</el-button
          >
        </el-form-item>

        <!-- Login Link -->
        <div class="footer-link">
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
            profile_pic: this.form.profile_pic || "",
            matricNo: this.form.role === "student" ? this.form.matricNo : null,
            staffNo: this.form.role !== "student" ? this.form.staffNo : null,
          };

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
          console.error(error);
          ElMessage.error("Registration failed. Server error.");
        } finally {
          this.loading = false;
        }
      });
    },
    goLogin() {
      this.$router.push("/login");
    },
    onAvatarChange(uploadFile) {
      const file = uploadFile.raw;

      if (!file.type.startsWith("image/")) {
        ElMessage.error("Only image files are allowed.");
        return;
      }

      const isLt2M = file.size / 1024 / 1024 < 2;
      if (!isLt2M) {
        ElMessage.error("Image size must be less than 2MB.");
        return;
      }

      const reader = new FileReader();
      reader.onload = () => {
        this.form.profile_pic = reader.result;
      };
      reader.readAsDataURL(file);
    },
  },
};
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(to right, #f4f6f9, #e0eafc);
  min-height: 100vh;
  padding: 40px 20px;
}

.register-card {
  width: 520px;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.register-header {
  text-align: center;
  margin-bottom: 24px;
}

.register-header h2 {
  margin-bottom: 4px;
  font-size: 28px;
  font-weight: 600;
}

.sub-title {
  color: #888;
  font-size: 14px;
}

.avatar-preview img {
  margin-top: 12px;
  max-width: 100px;
  border-radius: 8px;
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
}

.footer-link {
  text-align: right;
  margin-top: 10px;
}
</style>
