<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h2>Login - MarkMaster</h2>
      </div>

      <div class="form-container">
        <div class="form-group">
          <label>Role</label>
          <select v-model="loginData.role" class="form-select">
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="lecturer">Lecturer</option>
            <option value="academicAdvisor">Academic Advisor</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="form-group">
          <label>Identifier</label>
          <input
            v-model="loginData.identifier"
            type="text"
            placeholder="Enter Email / Matric No. / Staff No."
            class="form-input"
            @keyup.enter="performLogin"
          />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input
            v-model="loginData.password"
            type="password"
            placeholder="Enter your password"
            class="form-input"
            @keyup.enter="performLogin"
          />
        </div>

        <div class="form-group">
          <button
            @click="performLogin"
            :disabled="isSubmitting"
            class="login-button"
          >
            {{ isSubmitting ? 'Logging in...' : 'Login' }}
          </button>
        </div>

        <div class="links">
          <button type="button" @click="navigateToRegister" class="link-button">
            Don't have an account? Register
          </button>
          <button type="button" @click="navigateToForgotPassword" class="link-button">
            Forgot Password?
          </button>
        </div>

        <!-- Status Messages -->
        <div v-if="statusMessage" :class="['status-message', statusType]">
          {{ statusMessage }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoginView',
  data() {
    return {
      isSubmitting: false,
      statusMessage: '',
      statusType: '',
      loginData: {
        role: '',
        identifier: '',
        password: ''
      }
    }
  },
  methods: {
    showMessage(message, type = 'info') {
      this.statusMessage = message;
      this.statusType = type;
      setTimeout(() => {
        this.statusMessage = '';
        this.statusType = '';
      }, 5000);
    },

    validateInput() {
      if (!this.loginData.role) {
        this.showMessage('Please select your role', 'error');
        return false;
      }
      
      if (!this.loginData.identifier) {
        this.showMessage('Please enter your identifier', 'error');
        return false;
      }
      
      if (!this.loginData.password) {
        this.showMessage('Please enter your password', 'error');
        return false;
      }

      return true;
    },

    async performLogin() {
      if (!this.validateInput()) {
        return;
      }

      this.isSubmitting = true;
      this.showMessage('Logging in...', 'info');
      
      try {
        console.log('Login attempt:', {
          identifier: this.loginData.identifier,
          role: this.loginData.role
        });

        const requestData = {
          identifier: this.loginData.identifier.trim(),
          password: this.loginData.password,
          role: this.loginData.role
        };

        const response = await fetch('http://localhost:8085/login.php', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(requestData)
        });

        console.log('Response status:', response.status);
        
        if (response.status !== 200) {
          throw new Error('Server responded with status: ' + response.status);
        }

        const responseData = await response.json();
        console.log('Login response:', responseData);

        if (responseData.success) {
          this.handleLoginSuccess(responseData.user);
        } else {
          this.showMessage(responseData.message || 'Login failed', 'error');
        }
        
      } catch (error) {
        console.error('Login error:', error);
        this.showMessage('Login failed: ' + error.message, 'error');
      } finally {
        this.isSubmitting = false;
      }
    },

    handleLoginSuccess(userData) {
      try {
        // Store user data safely
        localStorage.setItem('user', JSON.stringify(userData));
        
        this.showMessage('Login successful! Redirecting...', 'success');
        
        // Navigate after a short delay
        setTimeout(() => {
          this.navigateBasedOnRole(userData.role);
        }, 1000);
        
      } catch (error) {
        console.error('Error handling login success:', error);
        this.showMessage('Login successful but navigation failed', 'error');
      }
    },

    navigateBasedOnRole(userRole) {
      try {
        if (userRole === 'admin') {
          this.$router.push('/admin/user-management');
        } else if (userRole === 'lecturer') {
          this.$router.push('/courses');
        } else if (userRole === 'academicAdvisor') {
          this.$router.push('/advisorworkspace');
        } else {
          this.$router.push('/dashboard');
        }
      } catch (error) {
        console.error('Navigation error:', error);
        // Fallback to window.location
        window.location.href = '/dashboard';
      }
    },

    navigateToRegister() {
      this.$router.push('/register');
    },

    navigateToForgotPassword() {
      this.$router.push('/forget-password');
    }
  }
}
</script>

<style scoped>
.login-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f9f6f1;
  color: #3a3a3a;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  font-size: 16px;
}

.login-card {
  width: 400px;
  padding: 32px;
  border-radius: 16px;
  background-color: #fff9f2;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.login-header {
  margin-bottom: 32px;
  text-align: center;
}

.login-header h2 {
  font-weight: 700;
  font-size: 30px;
  color: #3a3a3a;
  letter-spacing: 1px;
  margin: 0;
}

.form-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 600;
  color: #7e7e7e;
  font-size: 14px;
}

.form-input,
.form-select {
  padding: 12px;
  border: 1px solid #d6d0c6;
  border-radius: 10px;
  background-color: #fcf9f5;
  font-size: 14px;
  color: #3a3a3a;
  transition: border-color 0.3s ease;
  width: 100%;
  box-sizing: border-box;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #d6a77a;
}

.login-button {
  width: 100%;
  padding: 12px;
  background-color: #e7eaf0;
  color: #3a3a3a;
  font-weight: 600;
  border-radius: 10px;
  border: none;
  transition: background-color 0.3s ease;
  cursor: pointer;
  font-size: 16px;
}

.login-button:hover:not(:disabled) {
  background-color: #cfd8e3;
  color: #1a2533;
}

.login-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.links {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: flex-end;
}

.link-button {
  background: none;
  border: none;
  color: #7e7e7e;
  font-weight: 500;
  cursor: pointer;
  padding: 4px 0;
  font-size: 14px;
}

.link-button:hover {
  color: #d6a77a;
  text-decoration: underline;
}

.status-message {
  padding: 10px;
  border-radius: 5px;
  font-size: 14px;
  text-align: center;
  margin-top: 10px;
}

.status-message.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.status-message.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.status-message.info {
  background-color: #d1ecf1;
  color: #0c5460;
  border: 1px solid #bee5eb;
}
</style>