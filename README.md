# 更新日志

---

20260627<br>
<br>
1、完成对data-mock.js假数据的迁移，全部数据整合到sql表中<br>
2、完成html的功能的完整迁移<br>
3、完成既有页面下的BUG修复<br>


---

# Furniture Sales & Order Management System

本项目是一个基于 **HTML + CSS + JavaScript (前端模拟数据)** 的家具销售与订单管理系统示范。  
通过逐步拆分与优化，我们实现了清晰的页面结构、统一的样式管理，以及模块化的逻辑文件。

---

## 在线部署效果 Online deployment effect

[4523m.vercel.app](https://4523m.vercel.app)

## git 开发记录 Development records

```
48639e8 (HEAD -> local, Vercel/main, OnGitHub/main) Merge branch 'main' of github.com:xxxxxxxxxxxxxxxx into local
3233c39 Merge branch 'main' of ssh://ssh.github.com:443/xxxxxxxxxxxxxxxx/4523M into local
4c62fda Merge branch 'main' of ssh://ssh.github.com:443/xxxxxxxxxxxxxxxx/4523M into local
1713ee6 20260401-ui-zoomom_function-and-cart
ee6b9b5 20260401-ui-zoomom_function-and-cart
eda940b Merge branch 'main' of ssh://ssh.github.com:443/xxxxxxxxxxxxxxxx/4523M into local
70f116a 20260331-ui-拆分html
aafcce3 Initialize README with project details and instructions
de0fc57 (OnGitHub/local) 20260330-ui-html
```

## 📂 项目目录结构

```
│  index.html
│  login.html
│  order.html
│  register.html
│
├─assets
│  └─Sample_furntiure_images
│          1.png
│          2.png
│          3.png
│          4.png
│          5.png
│          6.png
│
├─css
│      login.css
│      style.css
│
├─js
│      auth.js
│      data-mock.js
│      main.js
│
└─廢棄版本的參考文件
        data-mock.md
        index.md
        main.md
        style.md
```


---

## 🚀 开发进度（对话过程回顾）

1. **初始阶段**  
   - 将单文件 HTML 拆分为 `index.html`、`data-mock.js`、`main.js`。  
   - 保留结构在 `index.html`，数据抽离到 `data-mock.js`，逻辑放入 `main.js`。

2. **样式优化**  
   - 将表单与下拉框统一使用 `.form-control` 样式。  
   - 使用 CSS `margin-bottom` 控制间距，避免 `<br>`。  
   - Cart Section 改为卡片布局，保持与 Furniture List 一致。

3. **页面拆分**  
   - 新建 `login.html`，并实现 Customer / Admin 登录标签切换。  
   - 新建 `register.html`，独立 Customer Registration 区块。  
   - 新建 `order.html`，独立 Place New Order 区块。  
   - 首页导航栏精简为 **Home / Login / Register / Cart**。

4. **交互逻辑**  
   - 编写 `auth.js` 示例，模拟用户登录验证与导航栏权限切换。  
   - 在 `login.html` 中实现标签页切换逻辑。  

---

## 🛠️ 使用说明

- 打开 `index.html` 查看家具列表与购物车入口。  
- 点击导航栏 **Login** 进入 `login.html`，可在标签页切换 Customer / Admin 登录。  
- 点击导航栏 **Register** 进入 `register.html`，完成用户注册。  
- 点击导航栏 **Cart** 或首页购物车入口进入 `order.html`，完成下单操作。  

---

## 📌 后续优化方向

- 将购物车逻辑与订单数据持久化（例如使用 LocalStorage 或后端 API）。  
- 增加订单管理后台页面，完善 Admin 功能。  
- 优化响应式布局，适配移动端。  
- 引入框架（如 Vue/React）进行组件化重构。

---

## 👨‍💻 作者与进度

- 项目由 **Stephen** 在 2026 年 3 月逐步拆分与优化完成。  
- 当前版本为前端示范，后续可扩展为全栈应用。

