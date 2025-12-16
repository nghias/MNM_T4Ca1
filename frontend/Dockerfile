# frontend/Dockerfile

# Dùng Nginx siêu nhẹ
FROM nginx:alpine

# Copy toàn bộ file trong thư mục frontend vào thư mục web của Nginx
# Vì file index.html nằm ngay ngoài cùng của folder frontend nên copy dấu chấm (.) là đủ
COPY . /usr/share/nginx/html

# Nginx mặc định chạy port 80
EXPOSE 80