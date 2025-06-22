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
        path: '/courses/edit/:id',
        name: 'EditCourse',
        component: () => import('@/views/AddCourseView.vue'), // 重用 AddCourseView.vue
        props: true
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

      // Admin routes
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

const router = createRouter({
  history: createWebHistory(),
  routes,
});

//  router guider ，based on token authentication
router.beforeEach((to, from, next) => {
  const userData = localStorage.getItem("user");
  const user = userData ? JSON.parse(userData) : null;
  const role = user?.role;

  const adminRoutes = ["/admin/user-management", "/admin/reset-password"];

  const lecturerRoutes = [
    "/marks",
    "/ca-components",
    "/final-exam",
    "course_management",
  ];

  const advisorRoutes = ["/advisorworkspace"];

  // 1. 未登录访问受保护页面 → 拦截
  if (to.path !== "/login" && to.path !== "/register" && !user) {
    return next("/login");
  }

  // 2. 已登录用户访问 login/register → 跳转 dashboard
  if ((to.path === "/login" || to.path === "/register") && user) {
    return next("/dashboard");
  }

  // 3. 角色访问控制（集中逻辑）
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

  // 4. 允许通行
  next();
});

export default router;
