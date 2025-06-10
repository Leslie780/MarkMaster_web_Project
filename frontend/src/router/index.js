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
import LecturersView from "@/views/LecturersView.vue";
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
  const user = localStorage.getItem("user");

  if (to.meta.requiresAuth && !user) {
    next("/login");
  } else if ((to.path === "/login" || to.path === "/register") && user) {
    next("/dashboard");
  } else {
    next();
  }
});

export default router;
