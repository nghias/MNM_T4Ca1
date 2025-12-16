# backend/Dockerfile

# 1. Dùng Node.js bản nhẹ
FROM node:18-alpine

# 2. Tạo thư mục làm việc trong container
WORKDIR /app

# 3. Copy file định nghĩa thư viện trước để tận dụng cache
COPY package*.json ./

# 4. Cài đặt thư viện (tạo node_modules mới chuẩn Linux)
RUN npm install

# 5. Copy toàn bộ code (trừ những thứ trong .dockerignore)
COPY . .

# 6. Mở port (thường Node.js express chạy 3000, bạn check trong file index.js nhé)
EXPOSE 3000

# 7. Lệnh chạy app (dựa vào file index.js trong ảnh)
CMD ["node", "index.js"]