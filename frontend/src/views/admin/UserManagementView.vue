<template>
  <div class="user-management">
    <el-card shadow="hover" class="main-card">
      <div class="header">
        <h2>User Management</h2>
        <div>
          <el-button
            type="info"
            @click="showLogsDialog"
            style="margin-right: 10px"
            >View System Logs</el-button
          >
          <el-button type="primary" @click="openDialog()">Add User</el-button>
        </div>
      </div>

      <div class="filter-section">
        <el-row :gutter="20">
          <el-col :span="8">
            <el-input
              v-model="searchQuery"
              placeholder="Search by name, email..."
              prefix-icon="el-icon-search"
              clearable
            />
          </el-col>
          <el-col :span="6">
            <el-select
              v-model="roleFilter"
              placeholder="Filter by Role"
              clearable
            >
              <el-option label="All Roles" value="" />
              <el-option label="Student" value="student" />
              <el-option label="Lecturer" value="lecturer" />
              <el-option label="Academic Advisor" value="academicAdvisor" />
              <el-option label="Admin" value="admin" />
            </el-select>
          </el-col>
          <el-col :span="6">
            <el-select
              v-model="statusFilter"
              placeholder="Filter by Status"
              clearable
            >
              <el-option label="All Status" value="" />
              <el-option label="Active" value="active" />
              <el-option label="Disabled" value="disabled" />
            </el-select>
          </el-col>
          <el-col :span="4">
            <el-button
              @click="toggleView"
              :icon="
                viewMode === 'table' ? 'el-icon-collection' : 'el-icon-menu'
              "
            >
              {{ viewMode === "table" ? "Card View" : "Table View" }}
            </el-button>
          </el-col>
        </el-row>
      </div>

      <div class="stats-section">
        <el-row :gutter="20">
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-number">{{ totalUsers }}</div>
                <div class="stat-label">Total Users</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-number">{{ activeUsers }}</div>
                <div class="stat-label">Active Users</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-number">{{ studentCount }}</div>
                <div class="stat-label">Students</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-number">{{ staffCount }}</div>
                <div class="stat-label">Staff</div>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </div>

      <div v-if="viewMode === 'table'">
        <el-table
          :data="filteredUsers"
          stripe
          v-loading="loading"
          style="width: 100%"
        >
          <el-table-column prop="id" label="ID" width="60" />
          <el-table-column prop="name" label="Name" />
          <el-table-column prop="email" label="Email" />
          <el-table-column prop="role" label="Role">
            <template #default="scope">
              <el-tag :type="getRoleTagType(scope.row.role)">
                {{ formatRole(scope.row.role) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="phone" label="Phone" />
          <el-table-column prop="status" label="Status">
            <template #default="scope">
              <el-tag
                :type="scope.row.status === 'active' ? 'success' : 'danger'"
              >
                {{ formatStatus(scope.row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Actions" width="180">
            <template #default="scope">
              <el-button size="small" @click="openDialog(scope.row)"
                >Edit</el-button
              >
              <el-button
                size="small"
                type="danger"
                @click="deleteUser(scope.row.id)"
                >Delete</el-button
              >
            </template>
          </el-table-column>
        </el-table>
      </div>

      <div v-else class="card-view">
        <el-row :gutter="20" v-loading="loading">
          <el-col :span="8" v-for="user in filteredUsers" :key="user.id">
            <el-card class="user-card" shadow="hover">
              <div class="user-card-header">
                <div class="user-avatar">
                  <img
                    v-if="user.profile_pic"
                    :src="`http://localhost:8085/uploads/${user.profile_pic}`"
                    alt="Profile"
                  />
                  <el-avatar v-else :size="60">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : "U" }}
                  </el-avatar>
                </div>
                <div class="user-status">
                  <el-tag
                    :type="user.status === 'active' ? 'success' : 'danger'"
                    size="small"
                  >
                    {{ formatStatus(user.status) }}
                  </el-tag>
                </div>
              </div>

              <div class="user-card-content">
                <h3 class="user-name">{{ user.name || "No Name" }}</h3>
                <p class="user-email">{{ user.email }}</p>
                <div class="user-role">
                  <el-tag :type="getRoleTagType(user.role)" size="small">
                    {{ formatRole(user.role) }}
                  </el-tag>
                </div>
                <div class="user-details">
                  <p v-if="user.phone">
                    <i class="el-icon-phone"></i> {{ user.phone }}
                  </p>
                  <p v-if="user.matric_no">
                    <i class="el-icon-user"></i> {{ user.matric_no }}
                  </p>
                  <p v-if="user.staff_no">
                    <i class="el-icon-user"></i> {{ user.staff_no }}
                  </p>
                </div>
              </div>

              <div class="user-card-actions">
                <el-button
                  size="small"
                  type="primary"
                  @click="openDialog(user)"
                >
                  <i class="el-icon-edit"></i> Edit
                </el-button>
                <el-button
                  size="small"
                  type="danger"
                  @click="deleteUser(user.id)"
                >
                  <i class="el-icon-delete"></i> Delete
                </el-button>
              </div>
            </el-card>
          </el-col>
        </el-row>

        <div v-if="filteredUsers.length === 0 && !loading" class="no-data">
          <el-empty description="No users found" />
        </div>
      </div>
    </el-card>

    <el-dialog
      v-model="dialogVisible"
      :title="isEditing ? 'Edit User' : 'Add User'"
      width="500px"
    >
      <el-form :model="form" ref="formRef" label-width="120px" :rules="rules">
        <el-form-item label="ID" v-if="isEditing">
          <el-input v-model="form.id" readonly />
        </el-form-item>

        <el-form-item label="Name" prop="name">
          <el-input v-model="form.name" placeholder="Full Name" />
        </el-form-item>

        <el-form-item label="Email" prop="email">
          <el-input v-model="form.email" placeholder="Email Address" />
        </el-form-item>

        <el-form-item label="Phone" prop="phone">
          <el-input v-model="form.phone" placeholder="Phone Number" />
        </el-form-item>

        <el-form-item label="Role" prop="role">
          <el-select
            v-model="form.role"
            placeholder="Select Role"
            :disabled="isEditing"
          >
            <el-option label="Student" value="student" />
            <el-option label="Lecturer" value="lecturer" />
            <el-option label="Academic Advisor" value="academicAdvisor" />
            <el-option label="Admin" value="admin" />
          </el-select>
        </el-form-item>

        <el-form-item label="Matric No" v-if="form.role === 'student'">
          <el-input v-model="form.matric_no" placeholder="Matric Number" />
        </el-form-item>

        <el-form-item label="Staff No" v-if="form.role !== 'student'">
          <el-input v-model="form.staff_no" placeholder="Staff Number" />
        </el-form-item>

        <el-form-item label="Status" prop="status">
          <el-select v-model="form.status" placeholder="Select Status">
            <el-option label="Active" value="active" />
            <el-option label="Disabled" value="disabled" />
          </el-select>
        </el-form-item>

        <el-form-item label="Password" prop="password" v-if="!isEditing">
          <el-input
            v-model="form.password"
            type="password"
            placeholder="Enter Password"
            show-password
          />
        </el-form-item>

        <el-form-item label="Profile Picture">
          <el-upload
            class="avatar-uploader"
            action="http://localhost:8085/upload"
            :show-file-list="false"
            :on-success="handleUploadSuccess"
          >
            <img
              v-if="form.profile_pic"
              :src="`http://localhost:8085/uploads/${form.profile_pic}`"
              class="avatar"
            />
            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
          </el-upload>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">Cancel</el-button>
        <el-button type="primary" @click="submitForm">Save</el-button>
      </template>
    </el-dialog>

    <el-dialog
      v-model="logsDialogVisible"
      title="System Activity Logs"
      width="70%"
    >
      <el-table :data="logs" v-loading="logsLoading" stripe height="50vh">
        <el-table-column prop="id" label="Log ID" width="80" />
        <el-table-column prop="action" label="Action" width="150">
          <template #default="scope">
            <el-tag :type="getActionTagType(scope.row.action)">
              {{ scope.row.action }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="details" label="Details" />
        <el-table-column prop="user_id" label="Admin ID" width="100" />
        <el-table-column prop="target_user_id" label="Target ID" width="100" />
        <el-table-column prop="ip_address" label="IP Address" width="140" />
        <el-table-column prop="created_at" label="Timestamp" width="180" />
      </el-table>
      <template #footer>
        <el-button @click="logsDialogVisible = false">Close</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { ElMessage, ElMessageBox } from "element-plus";

const users = ref([]);
const loading = ref(false);
const dialogVisible = ref(false);
const isEditing = ref(false);
const formRef = ref(null);
const viewMode = ref("table"); // 'table' or 'card'

// 筛选和搜索
const searchQuery = ref("");
const roleFilter = ref("");
const statusFilter = ref("");

// --- Refs for System Logs ---
const logsDialogVisible = ref(false);
const logsLoading = ref(false);
const logs = ref([]);

const defaultForm = () => ({
  id: null,
  name: "",
  email: "",
  phone: "",
  role: "student",
  matric_no: "",
  staff_no: "",
  status: "active",
  profile_pic: "",
  password: "",
});

const form = ref(defaultForm());

const rules = {
  name: [{ required: true, message: "Please enter name", trigger: "blur" }],
  email: [
    { required: true, message: "Please enter email", trigger: "blur" },
    { type: "email", message: "Invalid email", trigger: "blur" },
  ],
  role: [{ required: true, message: "Please select role", trigger: "change" }],
  status: [
    { required: true, message: "Please select status", trigger: "change" },
  ],
  password: [
    {
      validator: (rule, value, callback) => {
        if (!isEditing.value && !value) {
          callback(new Error("Password is required"));
        } else {
          callback();
        }
      },
      trigger: "blur",
    },
  ],
};

// 计算属性
const sortedUsers = computed(() =>
  [...users.value].sort((a, b) => a.id - b.id)
);

const filteredUsers = computed(() => {
  let filtered = sortedUsers.value;

  // 搜索筛选
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (user) =>
        (user.name && user.name.toLowerCase().includes(query)) ||
        (user.email && user.email.toLowerCase().includes(query)) ||
        (user.phone && user.phone.toLowerCase().includes(query))
    );
  }

  // 角色筛选
  if (roleFilter.value) {
    filtered = filtered.filter((user) => user.role === roleFilter.value);
  }

  // 状态筛选
  if (statusFilter.value) {
    filtered = filtered.filter((user) => user.status === statusFilter.value);
  }

  return filtered;
});

// 统计数据
const totalUsers = computed(() => users.value.length);
const activeUsers = computed(
  () => users.value.filter((u) => u.status === "active").length
);
const studentCount = computed(
  () => users.value.filter((u) => u.role === "student").length
);
const staffCount = computed(
  () => users.value.filter((u) => u.role !== "student").length
);

const formatRole = (role) =>
  ({
    student: "Student",
    lecturer: "Lecturer",
    academicAdvisor: "Academic Advisor",
    admin: "Admin",
  }[role] || role);

const formatStatus = (status) =>
  ({
    active: "Active",
    disabled: "Disabled",
  }[status] || status);

const getRoleTagType = (role) => {
  const types = {
    student: "",
    lecturer: "success",
    academicAdvisor: "warning",
    admin: "danger",
  };
  return types[role] || "info";
};

const toggleView = () => {
  viewMode.value = viewMode.value === "table" ? "card" : "table";
};

const fetchUsers = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get("http://localhost:8085/user-management");
    if (data.success) {
      users.value = data.users;
    } else {
      ElMessage.error(data.message || "Failed to load users");
    }
  } catch (err) {
    ElMessage.error("Network error while fetching users");
  } finally {
    loading.value = false;
  }
};

// --- MODIFIED: Functions to handle System Logs with Fallback ---
const fetchLogs = async () => {
  logsLoading.value = true;
  try {
    const { data } = await axios.get(
      "http://localhost:8085/user-management?action=logs"
    );
    if (data.success) {
      logs.value = data.logs;
    } else {
      ElMessage.error(data.message || "Failed to load logs");
    }
  } catch (err) {
    // THIS IS THE NEW FALLBACK LOGIC
    ElMessage.warning("Could not connect to the server. Displaying sample log data.");
    
    // Hardcoded data to display when the API call fails
    const fallbackLogs = [
      {
        id: 999,
        action: 'CREATE_USER',
        details: "Fallback: Admin created user 'Sample User 1' (ID: 101)",
        user_id: 1,
        target_user_id: 101,
        ip_address: '127.0.0.1',
        created_at: new Date().toISOString().replace('T', ' ').substring(0, 19),
      },
      {
        id: 998,
        action: 'UPDATE_USER',
        details: "Fallback: Admin updated user 'Sample User 2' (ID: 102)",
        user_id: 1,
        target_user_id: 102,
        ip_address: '127.0.0.1',
        created_at: new Date(Date.now() - 60000).toISOString().replace('T', ' ').substring(0, 19),
      },
      {
        id: 997,
        action: 'DELETE_USER',
        details: "Fallback: Admin deleted user 'Old Sample User' (ID: 103)",
        user_id: 1,
        target_user_id: 103,
        ip_address: '127.0.0.1',
        created_at: new Date(Date.now() - 120000).toISOString().replace('T', ' ').substring(0, 19),
      },
    ];
    logs.value = fallbackLogs;
  } finally {
    logsLoading.value = false;
  }
};

const showLogsDialog = () => {
  logsDialogVisible.value = true;
  fetchLogs();
};

const getActionTagType = (action) => {
  switch (action) {
    case "CREATE_USER":
      return "success";
    case "UPDATE_USER":
      return "warning";
    case "DELETE_USER":
      return "danger";
    default:
      return "info";
  }
};

const openDialog = (user = null) => {
  dialogVisible.value = true;
  isEditing.value = !!user;
  form.value = user ? { ...user, password: "" } : defaultForm();
};

const handleUploadSuccess = (res) => {
  form.value.profile_pic = res.filename;
};

const submitForm = () => {
  formRef.value.validate(async (valid) => {
    if (!valid) return;

    const payload = { ...form.value };

    if (isEditing.value) {
      delete payload.password;
    }

    for (const key in payload) {
      if (payload[key] === "") {
        payload[key] = null;
      }
    }

    try {
      const url = "http://localhost:8085/user-management";
      const res = isEditing.value
        ? await axios.put(url, payload)
        : await axios.post(url, payload);

      if (res.data.success) {
        ElMessage.success(res.data.message || "User saved successfully");
        dialogVisible.value = false;
        fetchUsers();
      } else {
        ElMessage.error(res.data.message || "Failed to save user");
      }
    } catch (err) {
      const msg = err.response?.data?.message || "Save failed";
      ElMessage.error(msg);
    }
  });
};

const deleteUser = (id) => {
  ElMessageBox.confirm("Are you sure to delete this user?", "Warning", {
    type: "warning",
  })
    .then(() => axios.delete(`http://localhost:8085/user-management?id=${id}`))
    .then((res) => {
      if (res.data.success) {
        ElMessage.success("User deleted");
        fetchUsers();
      } else {
        ElMessage.error(res.data.message || "Failed to delete");
      }
    })
    .catch(() => {});
};

onMounted(fetchUsers);
</script>

<style scoped>
.user-management {
  padding: 20px;
}

.main-card {
  min-height: 600px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filter-section {
  margin-bottom: 20px;
  padding: 20px;
  background: #f5f7fa;
  border-radius: 8px;
}

.stats-section {
  margin-bottom: 20px;
}

.stat-card {
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.stat-content {
  padding: 10px;
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #409eff;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

.card-view {
  margin-top: 20px;
}

.user-card {
  margin-bottom: 20px;
  transition: all 0.3s;
}

.user-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.user-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
}

.user-avatar img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
}

.user-card-content {
  margin-bottom: 15px;
}

.user-name {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.user-email {
  margin: 0 0 10px 0;
  color: #606266;
  font-size: 14px;
}

.user-role {
  margin-bottom: 10px;
}

.user-details p {
  margin: 5px 0;
  font-size: 13px;
  color: #909399;
}

.user-details i {
  margin-right: 5px;
  width: 16px;
}

.user-card-actions {
  display: flex;
  gap: 10px;
}

.user-card-actions .el-button {
  flex: 1;
}

.no-data {
  text-align: center;
  padding: 50px 0;
}

.avatar-uploader {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px dashed #dcdfe6;
  border-radius: 6px;
  width: 100px;
  height: 100px;
  cursor: pointer;
}

.avatar {
  width: 100px;
  height: 100px;
  border-radius: 6px;
  object-fit: cover;
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 100px;
  height: 100px;
  line-height: 100px;
  text-align: center;
}

@media (max-width: 768px) {
  .filter-section .el-col {
    margin-bottom: 10px;
  }

  .card-view .el-col {
    margin-bottom: 20px;
  }
}
</style>