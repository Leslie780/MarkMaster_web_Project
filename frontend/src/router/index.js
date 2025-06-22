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

// Safe function to get user from localStorage
function getStoredUser() {
  try {
    const userStr = localStorage.getItem("user");
    if (!userStr) return null;
    
    const user = JSON.parse(userStr);
    // Validate that user object has required properties
    if (user && typeof user === 'object' && user.id) {
      return user;
    }
    return null;
  } catch (error) {
    console.warn('Invalid user data in localStorage, clearing...', error);
    localStorage.removeItem("user");
    return null;
  }
}

// Router guard with safe localStorage handling
router.beforeEach((to, from, next) => {
  try {
    const user = getStoredUser();
    const isAuthRequired = to.meta && to.meta.requiresAuth;
    const isPublicRoute = ['/login', '/register', '/forget-password'].includes(to.path);
    
    if (isAuthRequired && !user) {
      // Redirect to login if authentication required but no user
      next("/login");
    } else if (isPublicRoute && user) {
      // Redirect to dashboard if user is logged in and trying to access public routes
      next("/dashboard");
    } else {
      // Allow navigation
      next();
    }
  } catch (error) {
    console.error('Router guard error:', error);
    // On any error, clear localStorage and redirect to login
    localStorage.removeItem("user");
    next("/login");
  }
});

export default router;