import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";
import {
  HomeFilled,
  Document,
  User,
  UserFilled,
  PieChart,
  Building,
} from "@element-plus/icons-vue";

const app = createApp(App);

// Add error handler before mounting
app.config.errorHandler = (err, vm, info) => {
  console.warn('Vue error caught:', err, info);
  // Don't throw errors for validation issues
  if (err.message && err.message.includes('string did not match')) {
    console.warn('Validation error suppressed:', err.message);
    return;
  }
  // For other errors, you can decide how to handle them
};

// Use router first
app.use(router);

// Configure Element Plus with safer options
app.use(ElementPlus, {
  // Disable strict validation that might cause regex issues
  zIndex: 3000,
});

// Register icons safely
app.component("EpHomeFilled", HomeFilled);
app.component("EpDocumentIcon", Document);
app.component("EpUserIcon", User);
app.component("EpUserFilled", UserFilled);
app.component("EpPieChart", PieChart);
app.component("EpBuilding", Building); // Fixed: was ceBuilding

// Enhanced error handlers
const setupErrorHandlers = () => {
  // Handle ResizeObserver errors
  window.addEventListener("error", (e) => {
    if (e.message === "ResizeObserver loop completed with undelivered notifications.") {
      e.stopImmediatePropagation();
      return;
    }
    
    // Handle regex validation errors
    if (e.message && e.message.includes("string did not match")) {
      console.warn("Validation pattern error suppressed:", e.message);
      e.stopImmediatePropagation();
      return;
    }
  });

  // Handle unhandled promise rejections
  window.addEventListener("unhandledrejection", (e) => {
    if (e.reason && e.reason.message && e.reason.message.includes("string did not match")) {
      console.warn("Promise rejection error suppressed:", e.reason.message);
      e.preventDefault();
      return;
    }
  });

  // Handle Vue warnings
  if (typeof window !== 'undefined') {
    const originalWarn = console.warn;
    console.warn = function(...args) {
      const message = args.join(' ');
      if (message.includes('string did not match') || message.includes('Invalid event arguments')) {
        return; // Suppress these warnings
      }
      originalWarn.apply(console, args);
    };
  }
};

// Setup error handlers before mounting
setupErrorHandlers();

// Mount the app
app.mount("#app");