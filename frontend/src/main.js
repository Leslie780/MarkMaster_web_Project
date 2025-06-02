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
} from "@element-plus/icons-vue";

const app = createApp(App);

app.use(router);
app.use(ElementPlus);

app.component("EpHomeFilled", HomeFilled);
app.component("EpDocumentIcon", Document);
app.component("EpUserIcon", User);
app.component("EpUserFilled", UserFilled);
app.component("EpPieChart", PieChart);

app.mount("#app");
