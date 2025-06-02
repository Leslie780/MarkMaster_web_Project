import { createRouter, createWebHistory } from "vue-router";

// Layout
import AppLayout from "@/layouts/AppLayout.vue";

// Public views
import LoginView from "@/views/LoginView.vue";
import RegisterView from "@/views/RegisterView.vue";

// Main views
import DashboardView from "@/views/DashboardView.vue";
import CoursesView from "@/views/CoursesView.vue";
import LecturersView from "@/views/LecturersView.vue";
import StudentsView from "@/views/StudentsView.vue";
import MarksView from "@/views/MarksView.vue";
import CAComponentsView from "@/views/CAComponentsView.vue";
import FinalExamView from "@/views/FinalExamView.vue";
import AddCourseView from "@/views/AddCourseView.vue";

// Admin views
import UserManagementView from "@/views/admin/UserManagementView.vue";
import AssignLecturersView from "@/views/admin/AssignLecturersView.vue";
import SystemLogsView from "@/views/admin/SystemLogsView.vue";
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
        path: "lecturers",
        name: "Lecturers",
        component: LecturersView,
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
        path: "admin/assign-lecturers",
        name: "AssignLecturers",
        component: AssignLecturersView,
      },
      {
        path: "admin/system-logs",
        name: "SystemLogs",
        component: SystemLogsView,
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

// Auth guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token");

  if (to.meta.requiresAuth && !token) {
    next("/login");
  } else if ((to.path === "/login" || to.path === "/register") && token) {
    next("/dashboard");
  } else {
    next();
  }
});

export default router;
