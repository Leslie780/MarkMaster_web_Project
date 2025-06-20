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
  ceBuilding,
} from "@element-plus/icons-vue";

const app = createApp(App);

app.use(router);
app.use(ElementPlus);

app.component("EpHomeFilled", HomeFilled);
app.component("EpDocumentIcon", Document);
app.component("EpUserIcon", User);
app.component("EpUserFilled", UserFilled);
app.component("EpPieChart", PieChart);
app.component("EpceBuilding", ceBuilding);

app.mount("#app");
const observerErrorHandler = () => {
  try {
    window.addEventListener("error", (e) => {
      if (
        e.message ===
        "ResizeObserver loop completed with undelivered notifications."
      ) {
        e.stopImmediatePropagation();
      }
    });
  } catch (err) {
    console.warn("ResizeObserver error handler not installed", err);
  }
};

observerErrorHandler();
