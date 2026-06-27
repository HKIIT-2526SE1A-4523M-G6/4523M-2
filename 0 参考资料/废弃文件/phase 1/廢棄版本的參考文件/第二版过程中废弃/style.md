/* ================================
   Global Design System - style.css
   ================================ */

/* 🎨 Color Variables */
:root {
  --primary: #2563eb;
  /* 企业蓝 */
  --accent: #d97706;
  /* 暖木色 */
  --success: #16a34a;
  /* 成功绿 */
  --warning: #facc15;
  /* 警告黄 */
  --error: #dc2626;
  /* 错误红 */

  --role-staff: #475569;
  /* 角色色 */
  --border-color: #e2e8f0;
  /* 细边框灰 */
  --radius: 0.5rem;
  /* 现代圆角 */
}

/* 🖋 Typography */
body {
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont,
    "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  color: #1e293b;
  background-color: #ffffff;
  margin: 0;
  padding: 0;
  line-height: 1.6;
}

/* 🔧 Utility Classes */
.hidden {
  display: none !important;
}

.flex-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.grid {
  display: grid;
  gap: 1.5rem;
}

/* 📦 Card Component */
.card {
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  background-color: #fff;
  padding: 1.5rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.2s ease;
}

.card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* 📱 Responsive Breakpoints */
@media (max-width: 768px) {

  .grid-3,
  .grid {
    grid-template-columns: 1fr !important;
  }
}