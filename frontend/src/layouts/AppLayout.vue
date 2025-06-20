<template>
  <div class="app-container">
    <!-- Header -->
    <div class="app-header">
      <div class="header-left">🎓 MarkMastert</div>

      <!-- 用户信息下拉（右上角） -->
      <div class="header-right">
        <el-dropdown trigger="click">
          <span class="user-info">
            👤 {{ username }}
            <el-icon><ArrowDown /></el-icon>
          </span>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item @click="goToResetPassword"
                >Change Password</el-dropdown-item
              >
              <el-dropdown-item divided @click="switchAccount"
                >Switch Account</el-dropdown-item
              >
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
    </div>

    <!-- Body -->
    <div class="app-body">
      <!-- Sidebar -->
      <div class="app-sidebar">
        <div class="menu-container">
          <el-menu
            :default-active="activeMenu"
            background-color="#001529"
            text-color="#fff"
            active-text-color="#409EFF"
            @select="handleMenuSelect"
            mode="vertical"
            class="main-menu"
          >
            <el-menu-item index="dashboard">
              <el-icon><HomeFilled /></el-icon>
              <span>Dashboard</span>
            </el-menu-item>
            <el-menu-item index="courses">
              <el-icon><Document /></el-icon>
              <span>Courses List</span>
            </el-menu-item>
            <el-menu-item index="course_management">
              <el-icon><UserFilled /></el-icon>
              <span>Course Management</span>
            </el-menu-item>
            <el-menu-item index="students">
              <el-icon><UserFilled /></el-icon>
              <span>Students</span>
            </el-menu-item>
            <el-menu-item index="marks">
              <el-icon><PieChart /></el-icon>
              <span>Marks</span>
            </el-menu-item>
            <el-menu-item index="advisorworkspace">
              <el-icon><User /></el-icon>
              <span>Advisor Workspace</span>
            </el-menu-item>
            <el-sub-menu index="assessments">
              <template #title>
                <el-icon><Edit /></el-icon>
                <span>Assessments</span>
              </template>
              <el-menu-item index="ca-components"
                >Continuous Assessment</el-menu-item
              >
              <el-menu-item index="final-exam">Final Exam</el-menu-item>
            </el-sub-menu>
            <el-sub-menu index="admin">
              <template #title>
                <el-icon><Setting /></el-icon>
                <span>Admin Panel</span>
              </template>
              <el-menu-item index="admin/user-management"
                >Manage Users</el-menu-item
              >
              <el-menu-item index="admin/reset-password"
                >Reset Password</el-menu-item
              >
            </el-sub-menu>
          </el-menu>
        </div>

        <!-- Logout at bottom -->
        <div class="logout-container">
          <el-menu
            background-color="#001529"
            text-color="#fff"
            active-text-color="#409EFF"
            @select="handleMenuSelect"
            mode="vertical"
          >
            <el-menu-item index="logout">
              <el-icon><SwitchButton /></el-icon>
              <span>Log out</span>
            </el-menu-item>
          </el-menu>
        </div>
      </div>

      <!-- Main Content -->
      <div class="app-main">
        <div class="main-content">
          <router-view v-slot="{ Component }">
            <component :is="Component" v-if="Component" />
            <div v-else class="no-content">
              <h3>Welcome to Course Mark Management System</h3>
              <p>Please select a menu item to get started.</p>
            </div>
          </router-view>
        </div>
        <div class="app-footer">© 2025 Course Mark Management System</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  HomeFilled,
  Document,
  User,
  UserFilled,
  PieChart,
  SwitchButton,
  Edit,
  Setting,
  ArrowDown,
} from "@element-plus/icons-vue";
import { useRouter, useRoute } from "vue-router";
import { ref, watch, onMounted } from "vue";
import { ElMessageBox } from "element-plus";

const router = useRouter();
const route = useRoute();
const activeMenu = ref("dashboard");
const username = ref("User");

onMounted(() => {
  const user = JSON.parse(localStorage.getItem("user"));
  if (user) {
    username.value =
      user.name || user.email || user.staff_no || user.matric_no || "User";
  }
});

watch(
  () => route.path,
  () => {
    const path =
      route.path.replace(/^\/|\/$/g, "").toLowerCase() || "dashboard";
    activeMenu.value = path;
  }
);

async function handleMenuSelect(index) {
  if (index === "logout") {
    try {
      await ElMessageBox.confirm(
        "Are you sure you want to log out?",
        "Confirm",
        {
          confirmButtonText: "Yes",
          cancelButtonText: "Cancel",
          type: "warning",
        }
      );

      localStorage.removeItem("token");
      localStorage.removeItem("user");
      router.replace("/login");
    } catch {
      // cancel
    }
  } else {
    router.push("/" + index).catch((err) => {
      if (err.name !== "NavigationDuplicated") console.error(err);
    });
  }
}

function switchAccount() {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  router.replace("/login");
}

function goToResetPassword() {
  router.push("/admin/reset-password");
}
</script>

<style scoped>
.app-container {
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 60px;
  background-color: #f7f7f7;
  color: #333;
  font-weight: 600;
  font-size: 20px;
  padding: 0 20px;
  border-bottom: 1px solid #ddd;
}

.header-left {
  flex-shrink: 0;
}

.header-right {
  font-size: 14px;
  color: #555;
  display: flex;
  align-items: center;
  cursor: pointer;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 4px;
}

.app-body {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.app-sidebar {
  width: 220px;
  background-color: #4a4a4a;
  color: #ddd;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
}

.menu-container {
  flex: 1;
  overflow-y: auto;
}

.main-menu {
  border: none;
  width: 100%;
  background-color: transparent !important;
}

.logout-container {
  border-top: 1px solid #666;
  margin-top: auto;
}

.app-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background-color: #f9f6f1;
}

.main-content {
  flex: 1;
  padding: 20px;
  background-color: #f9f6f1;
  overflow-y: auto;
  color: #444;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  font-size: 16px;
}

.app-footer {
  height: 48px;
  text-align: center;
  background-color: #f7f7f7;
  color: #666;
  line-height: 48px;
  border-top: 1px solid #ddd;
  font-size: 14px;
}

:deep(.el-menu-item),
:deep(.el-sub-menu__title) {
  color: #ddd !important;
}

:deep(.el-menu-item.is-active),
:deep(.el-menu-item:hover),
:deep(.el-sub-menu.is-opened > .el-sub-menu__title),
:deep(.el-sub-menu__title:hover) {
  background-color: #d6a77a !important;
  color: #3e2f1c !important;
}

:deep(.el-menu-item.is-active) > .el-icon,
:deep(.el-menu-item:hover) > .el-icon,
:deep(.el-sub-menu__title:hover) > .el-icon {
  color: #3e2f1c !important;
}

.menu-container::-webkit-scrollbar {
  width: 6px;
}
.menu-container::-webkit-scrollbar-track {
  background: #4a4a4a;
}
.menu-container::-webkit-scrollbar-thumb {
  background: #7a705c;
  border-radius: 3px;
}
.menu-container::-webkit-scrollbar-thumb:hover {
  background: #a88c6f;
}
</style>
