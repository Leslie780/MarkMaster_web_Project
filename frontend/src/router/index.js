import { createRouter, createWebHistory } from "vue-router";

// Layout
import AppLayout from "@/layouts/AppLayout.vue";

// Public views
import LoginView from "@/views/LoginView.vue";
import RegisterView from "@/views/RegisterView.vue";
import ForgetPasswordView from "@/views/ForgetPasswordView.vue";

// Main views
import DashboardView from "@/views/DashboardView.vue";
import CoursesView from "@/views/CoursesView.vue";
import CourseManagementView from "@/views/CourseManagement.vue";
import StudentsView from "@/views/StudentsView.vue";
import MarksView from "@/views/MarksView.vue";
import CAComponentsView from "@/views/CAComponentsView.vue";
import FinalExamView from "@/views/FinalExamView.vue";
import AddCourseView from "@/views/AddCourseView.vue";
import AdvisorWorkspaceView from "@/views/AdvisorWorkspaceView.vue";

// Admin views
import UserManagementView from "@/views/admin/UserManagementView.vue";
import ResetPasswordView from "@/views/admin/ResetPasswordsView.vue";

// 所有路由定义
const routes = [
  {
    path: "/login",
    name: "Login",
    component: LoginView,
  },
  {
    path: "/register",
    name: "Register",
    component: RegisterView,
  },
  {
    path: "/forget-password",
    name: "ForgetPassword",
    component: ForgetPasswordView,
  },
  {
    path: "/",
    component: AppLayout,
    meta: { requiresAuth: true },
    redirect: "/dashboard",
    children: [
      {
        path: "dashboard",
        name: "Dashboard",
        component: DashboardView,
      },
      {
        path: "advisorworkspace",
        name: "AdvisorWorkspace",
        component: AdvisorWorkspaceView,
      },
      {
        path: "courses",
        name: "Courses",
        component: CoursesView,
      },
      {
        path: "courses/add",
        name: "AddCourse",
        component: AddCourseView,
      },
      {
        path: "/courses/edit/:id",
        name: "EditCourse",
        component: () => import("@/views/AddCourseView.vue"),
        props: true,
      },
      {
        path: "course_management",
        name: "CourseManagement",
        component: CourseManagementView,
      },
      {
        path: "students",
        name: "Students",
        component: StudentsView,
      },
      {
        path: "marks",
        name: "Marks",
        component: MarksView,
      },
      {
        path: "ca-components",
        name: "CAComponents",
        component: CAComponentsView,
      },
      {
        path: "final-exam",
        name: "FinalExam",
        component: FinalExamView,
      },
      {
        path: "admin/user-management",
        name: "UserManagement",
        component: UserManagementView,
      },
      {
        path: "admin/reset-password",
        name: "ResetPassword",
        component: ResetPasswordView,
      },
    ],
  },
];

// 创建 Router 实例
const router = createRouter({
  history: createWebHistory(),
  routes,
});

// ➤ Token 过期判断函数
function isTokenExpired(exp) {
  return Date.now() > exp;
}

// ➤ 路由守卫：基于登录状态 + 角色 + token 过期
router.beforeEach((to, from, next) => {
  const userData = localStorage.getItem("user");
  const user = userData ? JSON.parse(userData) : null;

  // Token 过期自动登出
  if (user && isTokenExpired(user.exp)) {
    localStorage.removeItem("user");
    return next("/login");
  }

  const role = user?.role;

  const adminRoutes = ["/admin/user-management", "/admin/reset-password"];
  const lecturerRoutes = [
    "/marks",
    "/ca-components",
    "/final-exam",
    "course_management",
  ];
  const advisorRoutes = ["/advisorworkspace"];

  // 未登录访问受保护页面
  if (to.path !== "/login" && to.path !== "/register" && !user) {
    return next("/login");
  }

  // 已登录访问 login/register，跳转 dashboard
  if ((to.path === "/login" || to.path === "/register") && user) {
    return next("/dashboard");
  }

  // 角色访问权限判断
  if (adminRoutes.includes(to.path) && role !== "admin") {
    return next("/dashboard");
  }

  if (
    lecturerRoutes.includes(to.path) &&
    !["lecturer", "admin"].includes(role)
  ) {
    return next("/dashboard");
  }

  if (
    advisorRoutes.includes(to.path) &&
    !["academic advisor", "admin"].includes(role)
  ) {
    return next("/dashboard");
  }

  // 放行
  next();
});

export default router;
export { routes }; // 导出路由定义以供其他模块使用
