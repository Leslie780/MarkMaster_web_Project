<template>
  <div class="advisor-dashboard" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
   


    <el-row :gutter="16" class="mb-4">
      <el-col :span="8">
        <el-card>
          <div style="font-size: 18px; color: #777;">Candidate students</div>
          <div style="font-size: 32px; font-weight: bold;">{{ candidateStudents.length }}</div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card>
          <div style="font-size: 18px; color: #777;">My students</div>
          <div style="font-size: 32px; font-weight: bold;">{{ myStudents.length }}</div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card>
          <div style="font-size: 18px; color: #777;">Student currently selected</div>
          <div style="font-size: 32px; font-weight: bold;">
            {{ detailData ? detailData.name : '-' }}
          </div>
        </el-card>
      </el-col>
    </el-row>

  
    <el-card class="mb-4" shadow="hover">
      <div class="section-title">
        <el-icon><User /></el-icon>
        Candidate students
      </div>
      <el-table :data="candidateStudents" style="width: 100%;" size="small">
        <el-table-column prop="matric_no" label="Matric No." width="120" />
        <el-table-column prop="name" label="Name" width="150" />
        <el-table-column label="Operation" width="100">
          <template #default="{ row }">
            <el-button size="small" type="primary" @click="addStudent(row.student_id)">
              Add
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-empty v-if="candidateStudents.length === 0" description="No candidate students available">

      </el-empty>
    </el-card>


   
    <el-card class="mb-4" shadow="hover">
      <div class="section-title">
        <el-icon><UserFilled /></el-icon>
        My students
      </div>
      <el-table :data="myStudents" style="width: 100%;" size="small">
        <el-table-column prop="matric_no" label="Matric No." width="120" />
        <el-table-column prop="name" label="Name" width="150" />
        <el-table-column label="Operation" width="180">
          <template #default="{ row }">
            <el-button size="small" @click="showDetails(row.student_id)">details</el-button>
            <el-button size="small" type="danger" @click="removeStudent(row.student_id)">remove</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-empty v-if="myStudents.length === 0" description="No student yet"></el-empty>
    </el-card>



    <el-card v-if="detailData" shadow="hover">
      <div class="section-title">
        <el-icon><User /></el-icon>
        Student details info
      </div>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="Name">{{ detailData.name }}</el-descriptions-item>
        <el-descriptions-item label="Matric No.">{{ detailData.matric_no }}</el-descriptions-item>
        <el-descriptions-item label="Tel">{{ detailData.phone }}</el-descriptions-item>
      </el-descriptions>

      <div v-if="detailData.semesters && detailData.semesters.length" class="mb-2 mt-4">
        <h4>Semester Results</h4>
        <el-table :data="detailData.semesters" size="small" style="margin-bottom: 10px;">
          <el-table-column label="academic year/semester" min-width="120">
            <template #default="{ row }">
              {{ row.academic_year }} academic year -- {{ row.semester }}semester
            </template>
          </el-table-column>
          <el-table-column label="CGPA" prop="cgpa" width="80"/>
          <el-table-column label="academic status" width="120">
            <template #default="{ row }">
              <el-tag :type="row.academic_status === 'high-risk' ? 'danger' : 'success'">
                {{ row.academic_status === 'high-risk' ? 'High risk' : 'Active' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="course list">
            <template #default="{ row }">
              <el-table :data="row.courses" border size="mini">
                <el-table-column prop="course_name" label="course" />
                <el-table-column prop="course_code" label="course code" />
                <el-table-column prop="total_score" label="result" width="70"/>
                <el-table-column prop="grade" label="grade" width="60"/>
                <el-table-column prop="credit_hours" label="credit" width="60"/>
              </el-table>
            </template>
          </el-table-column>
        </el-table>
      </div>


     
<el-divider>Advisor Comment</el-divider>
<el-list size="small" bordered style="margin-bottom: 8px;">
  <el-list-item
  v-for="comment in detailData.advisor_comments"
  :key="comment.id"
  style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;"
>
  <span>
    {{ comment.content }}
    <span style="color: #888; font-size: 12px; margin-left: 10px;">（{{ comment.created_at }}）</span>
  </span>
  <el-button
    type="danger"
    size="mini"
    style="border-radius: 8px; margin-left: 12px; padding: 2px 12px;"
    @click="deleteComment(comment.id)"
  >delete</el-button>
</el-list-item>

  <el-empty v-if="!detailData.advisor_comments.length" description="No comment yet" />
</el-list>
<el-input
  v-model="commentInput"
  placeholder="add new comment"
  size="small"
  style="width: 240px; margin-right: 10px;"
/>
<el-button type="primary" size="small" @click="addComment">Add commment</el-button>


<el-divider>Appointment History</el-divider>
<el-list size="small" bordered style="margin-bottom: 8px;">
 <el-list-item
  v-for="meeting in detailData.advisor_appointments"
  :key="meeting.id"
  style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;"
>
  <span>
    {{ meeting.meeting_time }}: {{ meeting.content }}
  </span>
  <el-button
    type="danger"
    size="mini"
    style="border-radius: 8px; margin-left: 12px; padding: 2px 12px;"
    @click="deleteMeeting(meeting.id)"
  >delete</el-button>
</el-list-item>

  <el-empty v-if="!detailData.advisor_appointments.length" description="No appointments yet" />
</el-list>
<el-input
  v-model="meetingInput"
  placeholder="appointment info"
  size="small"
  style="width: 180px; margin-right: 8px;"
/>
<el-date-picker
  v-model="meetingTimeInput"
  type="date"
  placeholder="select date"
  size="small"
  style="width: 130px; margin-right: 10px;"
/>
<el-button type="primary" size="small" @click="addMeeting">Add appointment</el-button>

    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { User, UserFilled } from '@element-plus/icons-vue'

const user = JSON.parse(localStorage.getItem('user')) || {};
const advisor_id = user.id;

const candidateStudents = ref([])
const myStudents = ref([])
const detailData = ref(null)

const commentInput = ref('')
const meetingInput = ref('')
const meetingTimeInput = ref('')


function fetchCandidateStudents() {
  fetch('http://localhost:8085/advisor_api?action=candidate_list')
    .then(res => res.json())
    .then(data => {
      if (data.success) candidateStudents.value = data.data
    })
}


function fetchMyStudents() {
  fetch(`http://localhost:8085/advisor_api?action=students_detail&advisor_id=${advisor_id}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && Array.isArray(data.data)) {
        myStudents.value = data.data.map(stu => ({
          student_id: stu.student_id,
          name: stu.name,
          matric_no: stu.matric_no
        }))
      }
    })
}


function addStudent(student_id) {
  fetch('http://localhost:8085/advisor_api?action=add_student', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `advisor_id=${advisor_id}&student_id=${student_id}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) fetchMyStudents()
    })
}


function removeStudent(student_id) {
  fetch('http://localhost:8085/advisor_api?action=remove_student', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `advisor_id=${advisor_id}&student_id=${student_id}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        fetchMyStudents()
        if (detailData.value && detailData.value.student_id === student_id) detailData.value = null
      }
    })
}


function showDetails(student_id) {
  fetch(`http://localhost:8085/advisor_api?action=students_detail&advisor_id=${advisor_id}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && Array.isArray(data.data)) {
        const found = data.data.find(stu => stu.student_id === student_id)
        if (found) detailData.value = found
      }
    })
}


function addComment() {
  if (!detailData.value) return
  fetch('http://localhost:8085/advisor_api?action=add_comment', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `advisor_id=${advisor_id}&student_id=${detailData.value.student_id}&content=${encodeURIComponent(commentInput.value)}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        commentInput.value = ''
        showDetails(detailData.value.student_id)
      }
    })
}

function deleteComment(comment_id) {
  if (!detailData.value) return
  fetch('http://localhost:8085/advisor_api?action=delete_comment', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `advisor_id=${advisor_id}&comment_id=${comment_id}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) showDetails(detailData.value.student_id)
    })
}

function addMeeting() {
  if (!detailData.value) return
  if (!meetingTimeInput.value) {
    alert('Please slecet appointment date');
    return;
  }


  let dateStr = '';
  if (meetingTimeInput.value instanceof Date) {
    const d = meetingTimeInput.value
    dateStr = `${d.getFullYear()}-${(d.getMonth()+1).toString().padStart(2, '0')}-${d.getDate().toString().padStart(2, '0')}`
  } else if (typeof meetingTimeInput.value === 'string') {
    dateStr = meetingTimeInput.value
  }

  fetch('http://localhost:8085/advisor_api?action=add_meeting', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `advisor_id=${advisor_id}&student_id=${detailData.value.student_id}&content=${encodeURIComponent(meetingInput.value)}&meeting_time=${dateStr}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      meetingInput.value = ''
      meetingTimeInput.value = ''
      showDetails(detailData.value.student_id)
    }
  })
}


function deleteMeeting(meeting_id) {
  if (!detailData.value) return
  fetch('http://localhost:8085/advisor_api?action=delete_meeting', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `advisor_id=${advisor_id}&meeting_id=${meeting_id}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) showDetails(detailData.value.student_id)
    })
}

onMounted(() => {
  fetchCandidateStudents()
  fetchMyStudents()
})
</script>

<style scoped>
.mb-4 { margin-bottom: 24px; }
.mt-4 { margin-top: 24px; }
.section-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
