<template>
  <div class="advisor-dashboard">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="page-title">
            <el-icon class="title-icon"><UserFilled /></el-icon>
            Advisor Dashboard
          </h1>
          <p class="page-subtitle">
            Manage your students and track their academic progress
          </p>
        </div>
        <div class="header-stats">
          <div class="stat-card">
            <div class="stat-number">{{ candidateStudents.length }}</div>
            <div class="stat-label">Candidates</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ myStudents.length }}</div>
            <div class="stat-label">My Students</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ detailData ? "1" : "0" }}</div>
            <div class="stat-label">Selected</div>
          </div>
        </div>
        <div class="header-actions">
          <el-button
            type="success"
            size="large"
            class="export-button"
            @click="exportReports"
            :disabled="myStudents.length === 0"
            :loading="exportLoading"
          >
            <el-icon><Download /></el-icon>
            Export Reports
          </el-button>
        </div>
      </div>
    </div>

    <!-- 候选学生列表 -->
    <div class="components-section">
      <div class="component-card">
        <div class="card-header">
          <div class="component-info">
            <h2 class="component-title">
              <el-icon class="detail-icon"><User /></el-icon>
              Candidate Students
            </h2>
          </div>
        </div>
        <div class="card-content">
          <el-table
            :data="candidateStudents"
            style="width: 100%"
            size="default"
          >
            <el-table-column prop="matric_no" label="Matric No." width="150" />
            <el-table-column prop="name" label="Name" min-width="200" />
            <el-table-column label="Action" width="120">
              <template #default="{ row }">
                <el-button
                  size="small"
                  type="primary"
                  class="add-button"
                  @click="addStudent(row.student_id)"
                >
                  Add
                </el-button>
              </template>
            </el-table-column>
          </el-table>
          <div v-if="candidateStudents.length === 0" class="no-scores">
            No candidate students available
          </div>
        </div>
      </div>

      <!-- 我的学生列表 -->
      <div class="component-card">
        <div class="card-header">
          <div class="component-info">
            <h2 class="component-title">
              <el-icon class="detail-icon"><UserFilled /></el-icon>
              My Students
            </h2>
          </div>
        </div>
        <div class="card-content">
          <el-table :data="myStudents" style="width: 100%" size="default">
            <el-table-column prop="matric_no" label="Matric No." width="150" />
            <el-table-column prop="name" label="Name" min-width="200" />
            <el-table-column label="Actions" width="200">
              <template #default="{ row }">
                <el-button size="small" @click="showDetails(row.student_id)"
                  >Details</el-button
                >
                <el-button
                  size="small"
                  type="danger"
                  @click="removeStudent(row.student_id)"
                  >Remove</el-button
                >
              </template>
            </el-table-column>
          </el-table>
          <div v-if="myStudents.length === 0" class="no-scores">
            No students assigned yet
          </div>
        </div>
      </div>
    </div>

    <!-- 学生详情 -->
    <div v-if="detailData" class="components-section">
      <div class="component-card">
        <div class="card-header">
          <div class="component-info">
            <h2 class="component-title">
              <el-icon class="detail-icon"><User /></el-icon>
              Student Details - {{ detailData.name }}
            </h2>
          </div>
        </div>
        <div class="card-content">
          <!-- 基本信息 -->
          <div class="component-details">
            <div class="detail-item">
              <el-icon class="detail-icon"><User /></el-icon>
              <span class="detail-label">Name:</span>
              <span class="detail-value">{{ detailData.name }}</span>
            </div>
            <div class="detail-item">
              <el-icon class="detail-icon"><Document /></el-icon>
              <span class="detail-label">Matric No:</span>
              <span class="detail-value">{{ detailData.matric_no }}</span>
            </div>
            <div class="detail-item">
              <el-icon class="detail-icon"><Phone /></el-icon>
              <span class="detail-label">Phone:</span>
              <span class="detail-value">{{ detailData.phone }}</span>
            </div>
          </div>

          <!-- 学期成绩 -->
          <div
            v-if="detailData.semesters && detailData.semesters.length"
            class="scores-preview"
          >
            <div class="scores-header">
              <h4 class="scores-title">Academic Records</h4>
            </div>
            <el-table
              :data="detailData.semesters"
              size="small"
              style="margin-bottom: 20px"
            >
              <el-table-column label="Academic Year/Semester" min-width="200">
                <template #default="{ row }">
                  {{ row.academic_year }} - {{ row.semester }} Semester
                </template>
              </el-table-column>
              <el-table-column label="CGPA" prop="cgpa" width="100" />
              <el-table-column label="Status" width="120">
                <template #default="{ row }">
                  <el-tag
                    :type="
                      row.academic_status === 'high-risk' ? 'danger' : 'success'
                    "
                  >
                    {{
                      row.academic_status === "high-risk"
                        ? "High Risk"
                        : "Active"
                    }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column label="Courses">
                <template #default="{ row }">
                  <el-table :data="row.courses" border size="mini">
                    <el-table-column prop="course_name" label="Course" />
                    <el-table-column prop="course_code" label="Code" />
                    <el-table-column
                      prop="total_score"
                      label="Score"
                      width="80"
                    />
                    <el-table-column prop="grade" label="Grade" width="70" />
                    <el-table-column
                      prop="credit_hours"
                      label="Credits"
                      width="80"
                    />
                  </el-table>
                </template>
              </el-table-column>
            </el-table>
          </div>

          <!-- 导师评论 -->
          <div class="scores-preview">
            <div class="scores-header">
              <h4 class="scores-title">
                <el-icon class="detail-icon"><ChatLineRound /></el-icon>
                Advisor Comments
              </h4>
            </div>
            <div class="scores-list">
              <div
                v-for="comment in detailData.advisor_comments"
                :key="comment.id"
                class="score-item"
              >
                <div class="student-name">
                  {{ comment.content }}
                  <small style="color: #999; margin-left: 10px">{{
                    comment.created_at
                  }}</small>
                </div>
                <el-button
                  type="danger"
                  size="small"
                  @click="deleteComment(comment.id)"
                >
                  Delete
                </el-button>
              </div>
              <div v-if="!detailData.advisor_comments.length" class="no-scores">
                No comments yet
              </div>
            </div>
            <div style="margin-top: 16px; display: flex; gap: 12px">
              <el-input
                v-model="commentInput"
                placeholder="Add new comment"
                style="flex: 1"
              />
              <el-button type="primary" @click="addComment"
                >Add Comment</el-button
              >
            </div>
          </div>

          <!-- 预约历史 -->
          <div class="scores-preview" style="margin-top: 24px">
            <div class="scores-header">
              <h4 class="scores-title">
                <el-icon class="detail-icon"><Calendar /></el-icon>
                Appointment History
              </h4>
            </div>
            <div class="scores-list">
              <div
                v-for="meeting in detailData.advisor_appointments"
                :key="meeting.id"
                class="score-item"
              >
                <div class="student-name">
                  <strong>{{ meeting.meeting_time }}</strong
                  >: {{ meeting.content }}
                </div>
                <el-button
                  type="danger"
                  size="small"
                  @click="deleteMeeting(meeting.id)"
                >
                  Delete
                </el-button>
              </div>
              <div
                v-if="!detailData.advisor_appointments.length"
                class="no-scores"
              >
                No appointments yet
              </div>
            </div>
            <div
              style="
                margin-top: 16px;
                display: flex;
                gap: 12px;
                align-items: end;
              "
            >
              <el-input
                v-model="meetingInput"
                placeholder="Appointment description"
                style="flex: 1"
              />
              <el-date-picker
                v-model="meetingTimeInput"
                type="date"
                placeholder="Select date"
                style="width: 150px"
              />
              <el-button type="primary" @click="addMeeting"
                >Add Appointment</el-button
              >
            </div>
          </div>

          <!-- Export Individual Report -->
          <div class="card-footer">
            <el-button
              type="success"
              @click="exportIndividualReport(detailData.student_id)"
              style="width: 100%"
              :loading="individualExportLoading"
            >
              <el-icon><Download /></el-icon>
              Export Individual Consultation Report
            </el-button>
          </div>
        </div>
      </div>
    </div>

    <!-- 空状态提示 -->
    <div v-if="!detailData" class="empty-state">
      <div class="select-course-prompt">
        <el-icon class="prompt-icon"><Select /></el-icon>
        <p>Select a student from "My Students" to view detailed information</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import {
  User,
  UserFilled,
  Document,
  Phone,
  ChatLineRound,
  Calendar,
  Select,
  Download,
} from "@element-plus/icons-vue";
import { ElMessage } from 'element-plus';

const user = JSON.parse(localStorage.getItem("user")) || {};
const advisor_id = user.id;

const candidateStudents = ref([]);
const myStudents = ref([]);
const detailData = ref(null);
const exportLoading = ref(false);
const individualExportLoading = ref(false);

const commentInput = ref("");
const meetingInput = ref("");
const meetingTimeInput = ref("");

function fetchCandidateStudents() {
  fetch("http://localhost:8085/advisor_api?action=candidate_list")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) candidateStudents.value = data.data;
    })
    .catch((error) => {
      console.error('Error fetching candidate students:', error);
      ElMessage.error('Failed to fetch candidate students');
    });
}

function fetchMyStudents() {
  fetch(
    `http://localhost:8085/advisor_api?action=students_detail&advisor_id=${advisor_id}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.success && Array.isArray(data.data)) {
        myStudents.value = data.data.map((stu) => ({
          student_id: stu.student_id,
          name: stu.name,
          matric_no: stu.matric_no,
        }));
      }
    })
    .catch((error) => {
      console.error('Error fetching my students:', error);
      ElMessage.error('Failed to fetch student list');
    });
}

function addStudent(student_id) {
  fetch("http://localhost:8085/advisor_api?action=add_student", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `advisor_id=${advisor_id}&student_id=${student_id}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        fetchMyStudents();
        fetchCandidateStudents(); // Refresh candidates list
        ElMessage.success('Student added successfully');
      } else {
        ElMessage.error(data.message || 'Failed to add student');
      }
    })
    .catch((error) => {
      console.error('Error adding student:', error);
      ElMessage.error('Failed to add student');
    });
}

function removeStudent(student_id) {
  fetch("http://localhost:8085/advisor_api?action=remove_student", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `advisor_id=${advisor_id}&student_id=${student_id}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        fetchMyStudents();
        fetchCandidateStudents(); // Refresh candidates list
        if (detailData.value && detailData.value.student_id === student_id)
          detailData.value = null;
        ElMessage.success('Student removed successfully');
      } else {
        ElMessage.error(data.message || 'Failed to remove student');
      }
    })
    .catch((error) => {
      console.error('Error removing student:', error);
      ElMessage.error('Failed to remove student');
    });
}

function showDetails(student_id) {
  fetch(
    `http://localhost:8085/advisor_api?action=students_detail&advisor_id=${advisor_id}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.success && Array.isArray(data.data)) {
        const found = data.data.find((stu) => stu.student_id === student_id);
        if (found) detailData.value = found;
      }
    })
    .catch((error) => {
      console.error('Error fetching student details:', error);
      ElMessage.error('Failed to fetch student details');
    });
}

function addComment() {
  if (!detailData.value || !commentInput.value.trim()) {
    ElMessage.warning('Please enter a comment');
    return;
  }
  
  fetch("http://localhost:8085/advisor_api?action=add_comment", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `advisor_id=${advisor_id}&student_id=${
      detailData.value.student_id
    }&content=${encodeURIComponent(commentInput.value)}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        commentInput.value = "";
        showDetails(detailData.value.student_id);
        ElMessage.success('Comment added successfully');
      } else {
        ElMessage.error(data.message || 'Failed to add comment');
      }
    })
    .catch((error) => {
      console.error('Error adding comment:', error);
      ElMessage.error('Failed to add comment');
    });
}

function deleteComment(comment_id) {
  if (!detailData.value) return;
  fetch("http://localhost:8085/advisor_api?action=delete_comment", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `advisor_id=${advisor_id}&comment_id=${comment_id}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showDetails(detailData.value.student_id);
        ElMessage.success('Comment deleted successfully');
      } else {
        ElMessage.error(data.message || 'Failed to delete comment');
      }
    })
    .catch((error) => {
      console.error('Error deleting comment:', error);
      ElMessage.error('Failed to delete comment');
    });
}

function addMeeting() {
  if (!detailData.value || !meetingInput.value.trim()) {
    ElMessage.warning('Please enter appointment description');
    return;
  }
  
  if (!meetingTimeInput.value) {
    ElMessage.warning('Please select appointment date');
    return;
  }

  let dateStr = "";
  if (meetingTimeInput.value instanceof Date) {
    const d = meetingTimeInput.value;
    dateStr = `${d.getFullYear()}-${(d.getMonth() + 1)
      .toString()
      .padStart(2, "0")}-${d.getDate().toString().padStart(2, "0")}`;
  } else if (typeof meetingTimeInput.value === "string") {
    dateStr = meetingTimeInput.value;
  }

  fetch("http://localhost:8085/advisor_api?action=add_meeting", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `advisor_id=${advisor_id}&student_id=${
      detailData.value.student_id
    }&content=${encodeURIComponent(
      meetingInput.value
    )}&meeting_time=${dateStr}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        meetingInput.value = "";
        meetingTimeInput.value = "";
        showDetails(detailData.value.student_id);
        ElMessage.success('Appointment added successfully');
      } else {
        ElMessage.error(data.message || 'Failed to add appointment');
      }
    })
    .catch((error) => {
      console.error('Error adding appointment:', error);
      ElMessage.error('Failed to add appointment');
    });
}

function deleteMeeting(meeting_id) {
  if (!detailData.value) return;
  fetch("http://localhost:8085/advisor_api?action=delete_meeting", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `advisor_id=${advisor_id}&meeting_id=${meeting_id}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showDetails(detailData.value.student_id);
        ElMessage.success('Appointment deleted successfully');
      } else {
        ElMessage.error(data.message || 'Failed to delete appointment');
      }
    })
    .catch((error) => {
      console.error('Error deleting appointment:', error);
      ElMessage.error('Failed to delete appointment');
    });
}

// Export functions
function exportReports() {
  exportLoading.value = true;
  
  fetch(
    `http://localhost:8085/advisor_api?action=export_all_reports&advisor_id=${advisor_id}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        generateAllReportsCSV(data.data);
        ElMessage.success('Reports exported successfully');
      } else {
        ElMessage.error('Failed to export reports: ' + (data.message || 'Unknown error'));
        // Fallback: generate report from current data
        generateAllReportsFromCurrentData();
      }
    })
    .catch((error) => {
      console.error("Export error:", error);
      ElMessage.warning('API error, generating report from current data');
      // Fallback: generate report from current data
      generateAllReportsFromCurrentData();
    })
    .finally(() => {
      exportLoading.value = false;
    });
}

function exportIndividualReport(student_id) {
  individualExportLoading.value = true;
  
  fetch(
    `http://localhost:8085/advisor_api?action=export_student_report&advisor_id=${advisor_id}&student_id=${student_id}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        generateIndividualReportCSV(data.data);
        ElMessage.success('Individual report exported successfully');
      } else {
        ElMessage.error('Failed to export individual report: ' + (data.message || 'Unknown error'));
        // Fallback: generate report from current detail data
        if (detailData.value) {
          generateIndividualReportFromCurrentData(detailData.value);
        }
      }
    })
    .catch((error) => {
      console.error("Export error:", error);
      ElMessage.warning('API error, generating report from current data');
      // Fallback: generate report from current detail data
      if (detailData.value) {
        generateIndividualReportFromCurrentData(detailData.value);
      }
    })
    .finally(() => {
      individualExportLoading.value = false;
    });
}

function generateAllReportsCSV(data) {
  const csvContent = generateCSVContent(data, "all");
  downloadCSV(
    csvContent,
    `All_Students_Consultation_Reports_${
      new Date().toISOString().split("T")[0]
    }.csv`
  );
}

function generateIndividualReportCSV(data) {
  const csvContent = generateDetailedCSVContent(data);
  const studentName = data.name.replace(/[^a-zA-Z0-9]/g, "_");
  downloadCSV(
    csvContent,
    `${studentName}_Consultation_Report_${
      new Date().toISOString().split("T")[0]
    }.csv`
  );
}

function generateAllReportsFromCurrentData() {
  // Fallback function using current myStudents data
  const reportData = myStudents.value.map((student) => ({
    name: student.name,
    matric_no: student.matric_no,
    student_id: student.student_id,
    total_comments: 0,
    total_appointments: 0,
    last_consultation: "N/A",
    current_cgpa: "N/A",
    academic_status: "N/A"
  }));

  const csvContent = generateCSVContent(reportData, "summary");
  downloadCSV(
    csvContent,
    `Students_Summary_Report_${new Date().toISOString().split("T")[0]}.csv`
  );
}

function generateIndividualReportFromCurrentData(student) {
  const csvContent = generateDetailedCSVContent(student);
  const studentName = student.name.replace(/[^a-zA-Z0-9]/g, "_");
  downloadCSV(
    csvContent,
    `${studentName}_Detailed_Report_${
      new Date().toISOString().split("T")[0]
    }.csv`
  );
}

function generateCSVContent(data, type) {
  let headers = [];
  let rows = [];

  if (type === "all" || type === "summary") {
    headers = [
      "Student Name",
      "Matric No",
      "Total Comments",
      "Total Appointments",
      "Last Consultation",
      "Current CGPA",
      "Academic Status",
    ];

    rows = data.map((student) => [
      student.name || "",
      student.matric_no || "",
      student.total_comments || 0,
      student.total_appointments || 0,
      student.last_consultation || "N/A",
      student.current_cgpa || "N/A",
      student.academic_status || "N/A",
    ]);
  }

  return [headers, ...rows]
    .map((row) =>
      row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(",")
    )
    .join("\n");
}

function generateDetailedCSVContent(student) {
  const headers = ["Section", "Type", "Content", "Date", "Additional Info"];
  const rows = [];

  // Basic information
  rows.push(["Basic Info", "Name", student.name, "", ""]);
  rows.push(["Basic Info", "Matric No", student.matric_no, "", ""]);
  rows.push(["Basic Info", "Phone", student.phone || "N/A", "", ""]);
  rows.push(["Basic Info", "Email", student.email || "N/A", "", ""]);

  // Academic records
  if (student.semesters && student.semesters.length > 0) {
    student.semesters.forEach((semester) => {
      rows.push([
        "Academic Record",
        "Semester",
        `${semester.academic_year} - ${semester.semester}`,
        "",
        `CGPA: ${semester.cgpa}, Status: ${semester.academic_status}`,
      ]);

      if (semester.courses && semester.courses.length > 0) {
        semester.courses.forEach((course) => {
          rows.push([
            "Course",
            course.course_code,
            course.course_name,
            "",
            `Score: ${course.total_score}, Grade: ${course.grade}, Credits: ${course.credit_hours}`,
          ]);
        });
      }
    });
  }

  // Comments
  if (student.advisor_comments && student.advisor_comments.length > 0) {
    student.advisor_comments.forEach((comment) => {
      rows.push([
        "Comment",
        "Advisor Comment",
        comment.content,
        comment.created_at,
        "",
      ]);
    });
  }

  // Appointments
  if (student.advisor_appointments && student.advisor_appointments.length > 0) {
    student.advisor_appointments.forEach((appointment) => {
      rows.push([
        "Appointment",
        "Meeting",
        appointment.content,
        appointment.meeting_time,
        `Created: ${appointment.created_at}`,
      ]);
    });
  }

  return [headers, ...rows]
    .map((row) =>
      row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(",")
    )
    .join("\n");
}

function downloadCSV(csvContent, filename) {
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");

  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url); // Clean up
  }
}

onMounted(() => {
  fetchCandidateStudents();
  fetchMyStudents();
});
</script>

<style scoped>
.advisor-dashboard {
  padding: 24px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}

/* 页面头部 */
.page-header {
  margin-bottom: 32px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 32px;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.header-actions {
  margin-left: 24px;
}

.export-button {
  height: 48px;
  padding: 0 24px;
  border-radius: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.title-section {
  flex: 1;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-icon {
  color: #409eff;
  font-size: 36px;
}

.page-subtitle {
  font-size: 16px;
  color: #7f8c8d;
  margin: 0;
}

.header-stats {
  display: flex;
  gap: 24px;
}

.stat-card {
  text-align: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 24px;
  border-radius: 12px;
  min-width: 100px;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
}

/* 组件网格 */
.components-section {
  margin-bottom: 32px;
}

.component-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
  margin-bottom: 24px;
}

.component-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  padding: 24px 24px 16px;
  border-bottom: 1px solid #f0f2f5;
}

.component-info {
  flex: 1;
}

.component-title {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-content {
  padding: 24px;
}

.component-details {
  margin-bottom: 20px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 14px;
}

.detail-icon {
  color: #409eff;
  font-size: 16px;
}

.detail-label {
  color: #7f8c8d;
  font-weight: 500;
  min-width: 80px;
}

.detail-value {
  color: #2c3e50;
  font-weight: 600;
}

.scores-preview {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}

.scores-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.scores-title {
  font-weight: 500;
  color: #2c3e50;
  font-size: 14px;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.scores-list > .score-item:not(:last-child) {
  margin-bottom: 8px;
}

.score-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: white;
  border-radius: 8px;
  font-size: 13px;
}

.student-name {
  color: #2c3e50;
  flex: 1;
}

.no-scores {
  text-align: center;
  color: #95a5a6;
  padding: 16px;
  font-size: 14px;
}

.add-button {
  border-radius: 8px;
  font-weight: 500;
}

.card-footer {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f0f2f5;
}

/* 空状态 */
.empty-state {
  margin-top: 60px;
}

.select-course-prompt {
  text-align: center;
  padding: 80px 20px;
  color: #7f8c8d;
}

.prompt-icon {
  font-size: 64px;
  color: #ddd;
  margin-bottom: 16px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .advisor-dashboard {
    padding: 16px;
  }

  .header-content {
    flex-direction: column;
    gap: 24px;
    text-align: center;
  }

  .header-stats {
    justify-content: center;
  }
}
</style>