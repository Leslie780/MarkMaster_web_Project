<template>
  <div class="app-container">
    <!-- Header -->
    <div class="app-header">🎓 MarkMastert</div>

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
              <span>Courses</span>
            </el-menu-item>

            <el-menu-item index="lecturers">
              <el-icon><User /></el-icon>
              <span>Lecturers</span>
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

      <!-- Main content area -->
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
} from "@element-plus/icons-vue";
import { useRouter, useRoute } from "vue-router";
import { ref, watch, onMounted } from "vue";
import { ElMessageBox } from "element-plus";

const router = useRouter();
const route = useRoute();
const activeMenu = ref("dashboard");

function normalizePath(path) {
  return path.toLowerCase().replace(/^\/|\/$/g, "");
}

function updateActiveMenu() {
  let path = normalizePath(route.path);
  if (!path) path = "dashboard";
  activeMenu.value = path;
}

onMounted(updateActiveMenu);

watch(
  () => route.path,
  () => updateActiveMenu()
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
      // 用户点击取消
    }
  } else {
    router.push("/" + index).catch((err) => {
      if (err.name !== "NavigationDuplicated") console.error(err);
    });
  }
}
</script>

<style scoped>
.app-header {
  height: 60px;
  background-color: #f7f7f7;
  color: #333;
  font-weight: 600;
  font-size: 20px;
  line-height: 60px;
  padding: 0 20px;
  flex-shrink: 0;
  border-bottom: 1px solid #ddd;
}

.app-container {
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
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

* {
  box-sizing: border-box;
}

html,
body {
  margin: 0;
  padding: 0;
  height: 100%;
}

#app {
  height: 100%;
}

:deep(.el-menu-item) {
  height: 50px;
  line-height: 50px;
}

:deep(.el-sub-menu .el-sub-menu__title) {
  height: 50px;
  line-height: 50px;
}
</style>
