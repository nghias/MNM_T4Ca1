const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
require('dotenv').config();

const app = express();

// --- MIDDLEWARE ---
app.use(express.json());
// Cho phép tất cả các domain (bao gồm Somee) gọi API
app.use(cors({ origin: '*' }));

// --- KẾT NỐI MONGODB ---
// Sẽ lấy link kết nối từ file .env hoặc biến môi trường trên Render
const mongoURI = process.env.MONGO_URI;

if (!mongoURI) {
    console.error("❌ Lỗi: Chưa cấu hình MONGO_URI trong file .env");
} else {
    mongoose.connect(mongoURI)
        .then(() => console.log("✅ Đã kết nối MongoDB thành công"))
        .catch(err => console.error("❌ Lỗi kết nối MongoDB:", err));
}

// --- DATABASE MODEL (Sản phẩm) ---
const ProductSchema = new mongoose.Schema({
    name: { type: String, required: true },
    price: { type: Number, required: true },
    description: String
}, { timestamps: true });

const Product = mongoose.model('Product', ProductSchema);

// --- API ROUTES ---

// Route trang chủ để test xem server sống hay chết
app.get('/', (req, res) => {
    res.send('Backend Server is Running!');
});

// API lấy danh sách sản phẩm
app.get('/api/products', async (req, res) => {
    try {
        const products = await Product.find().sort({ createdAt: -1 });
        res.json(products);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// API thêm sản phẩm mới
app.post('/api/products', async (req, res) => {
    try {
        const { name, price, description } = req.body;
        const newProduct = new Product({ name, price, description });
        await newProduct.save();
        res.status(201).json(newProduct);
    } catch (err) {
        res.status(400).json({ error: err.message });
    }
});

// --- CHẠY SERVER ---
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`🚀 Server đang chạy tại http://localhost:${PORT}`);
});