# 📥 HƯỚNG DẪN DOWNLOAD PROJECT

## ⚠️ Vấn đề hiện tại
Server không thể truy cập từ internet (SSH timeout, HTTP không hoạt động).

## ✅ GIẢI PHÁP DOWNLOAD

### **Phương án 1: Sử dụng Claude Code Interface (Khuyên dùng)**

Bạn đang sử dụng Claude Code CLI. Để download file ZIP:

1. **Nếu dùng VS Code với Claude Code extension:**
   - Click chuột phải vào file `multilingual-chat-platform-full.zip` trong Explorer
   - Chọn "Download..."
   - File sẽ tải về máy local

2. **Nếu dùng Claude Code trên web:**
   - Sử dụng lệnh download trong interface
   - Hoặc request Claude Code tải file về

---

### **Phương án 2: Git Clone (Không cần ZIP)**

```bash
# Clone toàn bộ project từ GitHub
git clone https://github.com/huyhz123/chatbox.git
cd chatbox
git checkout claude/build-multilingual-chat-app-S6lSu

# Install dependencies
composer install
npm install

# Setup
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Run
make install  # Docker
# Hoặc: php artisan serve  # Manual
```

---

### **Phương án 3: Split ZIP thành nhiều file nhỏ**

Tạo các file nhỏ hơn để dễ download:

```bash
# Split ZIP thành files 100KB
cd /home/user/chatbox
split -b 100k multilingual-chat-platform-full.zip part_

# List parts
ls -lh part_*

# Sau đó download từng part và ghép lại trên Windows:
# copy /b part_aa+part_ab+part_ac+... multilingual-chat-platform-full.zip
```

---

### **Phương án 4: Tạo lại project từ source**

Không cần ZIP, tạo project mới từ source code sẵn có:

```bash
# Tất cả source code đã có trong:
/home/user/chatbox/

# Bạn có thể:
1. Clone từ GitHub
2. Hoặc copy từng thư mục quan trọng
3. Hoặc tạo archive nhỏ hơn (chỉ code, không có docs)
```

---

### **Phương án 5: Upload lên GitHub Release**

```bash
# Tạo GitHub Release với ZIP file
gh release create v1.0.0 \
  multilingual-chat-platform-full.zip \
  --title "Multilingual Chat Platform v1.0.0" \
  --notes "Complete project package"

# Sau đó download từ GitHub Releases page
```

---

### **Phương án 6: Upload lên Google Drive / Dropbox**

Sử dụng rclone hoặc gdrive để upload:

```bash
# Cài rclone
curl https://rclone.org/install.sh | sudo bash

# Configure
rclone config

# Upload
rclone copy multilingual-chat-platform-full.zip gdrive:
```

---

## 🎯 KHUYẾN NGHỊ

**Cách nhanh nhất:** Sử dụng Git Clone

```bash
git clone https://github.com/huyhz123/chatbox.git
cd chatbox
composer install && npm install
make install
```

**Ưu điểm:**
✅ Không cần download ZIP
✅ Luôn có phiên bản mới nhất
✅ Có git history
✅ Dễ update sau này

**File ZIP chỉ cần khi:**
- Muốn archive offline
- Không dùng Git
- Cần distribute cho người khác

---

## 📞 HỖ TRỢ

Nếu cần download ZIP bằng mọi giá, tôi có thể:
1. ✅ Split thành nhiều file nhỏ
2. ✅ Upload lên GitHub Release
3. ✅ Tạo base64 text file (lớn, nhưng có thể copy-paste)
4. ✅ Upload lên cloud storage

Cho tôi biết bạn chọn phương án nào!
