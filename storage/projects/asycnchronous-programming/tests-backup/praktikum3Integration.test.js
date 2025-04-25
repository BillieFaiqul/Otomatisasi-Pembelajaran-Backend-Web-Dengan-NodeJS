const mongoose = require("mongoose");
const request = require("supertest");
const { app } = require("../app");
const Meja = require("../src/models/mejaModel");
const connectDB = require("../src/config/database");

describe("Pengujian Integrasi - API Meja", () => {
  beforeAll(async () => {
    await connectDB();
    await Meja.deleteMany({}); // Pastikan database dalam keadaan kosong sebelum pengujian
  });

  beforeEach(async () => {
    await Meja.deleteMany({}); // Hapus semua data sebelum setiap pengujian
  });

  afterAll(async () => {
    await mongoose.connection.close(); // Tutup koneksi database setelah semua pengujian selesai
  });

  describe("POST add/meja", () => {
    test("Harus berhasil membuat data meja baru", async () => {
      const dataMeja = {
        tableNumber: 1,
        capacity: 4
      };

      const response = await request(app)
        .post("/add/meja")
        .send(dataMeja);

      expect(response.status).toBe(201);
      expect(response.body.success).toBe(true);
      expect(response.body.data).toHaveProperty("tableNumber", 1);
      expect(response.body.data).toHaveProperty("capacity", 4);
      expect(response.body.data).toHaveProperty("status", "available");
    });

    test("Harus gagal ketika data tableNumber tidak disediakan", async () => {
      const dataMeja = {
        capacity: 4
      };

      const response = await request(app)
        .post("/add/meja")
        .send(dataMeja);

      expect(response.status).toBe(400);
      expect(response.body.success).toBe(false);
      expect(response.body.error).toBeDefined();
    });

    test("Harus gagal ketika nomor meja sudah ada", async () => {
      // Buat meja pertama
      await Meja.create({
        tableNumber: 5,
        capacity: 4
      });

      // Coba buat meja dengan nomor yang sama
      const response = await request(app)
        .post("/add/meja")
        .send({
          tableNumber: 5,
          capacity: 2
        });

      expect(response.status).toBe(400);
      expect(response.body.success).toBe(false);
    });
  });

  describe("GET /meja", () => {
    test("Harus mendapatkan daftar meja kosong", async () => {
      const response = await request(app).get("/meja");

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data).toEqual([]);
    });

    test("Harus mendapatkan semua data meja", async () => {
      // Tambahkan beberapa meja untuk pengujian
      await Meja.create([
        { tableNumber: 1, capacity: 2 },
        { tableNumber: 2, capacity: 4 },
        { tableNumber: 3, capacity: 6 }
      ]);

      const response = await request(app).get("/meja");

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.length).toBe(3);
      expect(response.body.data[0]).toHaveProperty("tableNumber", 1);
      expect(response.body.data[1]).toHaveProperty("tableNumber", 2);
      expect(response.body.data[2]).toHaveProperty("tableNumber", 3);
    });
  });

  describe("PUT /meja/:tableNumber/reserve", () => {
    test("Harus berhasil memesan meja yang tersedia", async () => {
      // Buat data meja terlebih dahulu
      await Meja.create({
        tableNumber: 10,
        capacity: 4,
        status: "available",
      });
  
      const response = await request(app)
        .put("/meja/10/reserve")
        .send({ customerName: "John Doe" }); // Tambahkan customerName
  
      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data).toHaveProperty("tableNumber", 10);
      expect(response.body.data).toHaveProperty("status", "reserved");
      expect(response.body.data).toHaveProperty("customerName", "John Doe"); // Verifikasi customerName
    });
  
    test("Harus gagal memesan meja yang tidak ada", async () => {
      const response = await request(app)
        .put("/meja/99/reserve")
        .send({ customerName: "John Doe" }); // Tambahkan customerName
  
      expect(response.status).toBe(404);
      expect(response.body.success).toBe(false);
      expect(response.body.error).toBe("Meja tidak tersedia");
    });
  
    test("Harus gagal memesan meja yang sudah dipesan", async () => {
      // Buat meja yang sudah dipesan
      await Meja.create({
        tableNumber: 11,
        capacity: 4,
        status: "reserved",
      });
  
      const response = await request(app)
        .put("/meja/11/reserve")
        .send({ customerName: "John Doe" }); // Tambahkan customerName
  
      expect(response.status).toBe(404);
      expect(response.body.success).toBe(false);
      expect(response.body.error).toBe("Meja tidak tersedia");
    });
  
    test("Harus gagal memesan meja jika customerName tidak disediakan", async () => {
      // Buat data meja terlebih dahulu
      await Meja.create({
        tableNumber: 14,
        capacity: 4,
        status: "available",
      });
  
      const response = await request(app)
        .put("/meja/14/reserve")
        .send({}); // Tidak menyertakan customerName
  
      expect(response.status).toBe(400);
      expect(response.body.success).toBe(false);
      expect(response.body.error).toBe("Nama pelanggan harus diisi");
    });
  });
  
  describe("PUT /meja/:tableNumber/cancel", () => {
    test("Harus berhasil membatalkan reservasi meja", async () => {
      // Buat meja yang sudah dipesan
      await Meja.create({
        tableNumber: 12,
        capacity: 6,
        status: "reserved",
        customerName: "John Doe", // Tambahkan customerName
      });
  
      const response = await request(app)
        .put("/meja/12/cancel")
        .send({});
  
      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data).toHaveProperty("tableNumber", 12);
      expect(response.body.data).toHaveProperty("status", "available");
      expect(response.body.message).toBe("Reservation for table 12 has been cancelled");
    });
  
    test("Harus gagal membatalkan reservasi meja yang tidak ada", async () => {
      const response = await request(app)
        .put("/meja/99/cancel")
        .send({});
  
      expect(response.status).toBe(404);
      expect(response.body.success).toBe(false);
      expect(response.body.error).toBe("Table not found or not currently reserved");
    });
  
    test("Harus gagal membatalkan reservasi meja dengan status available", async () => {
      // Buat meja dengan status available
      await Meja.create({
        tableNumber: 13,
        capacity: 4,
        status: "available",
      });
  
      const response = await request(app)
        .put("/meja/13/cancel")
        .send({});
  
      expect(response.status).toBe(404);
      expect(response.body.success).toBe(false);
      expect(response.body.error).toBe("Table not found or not currently reserved");
    });
  });
  
  describe("Skenario Alur Pengelolaan Meja", () => {
    test("Harus menjalankan alur lengkap: membuat, memesan, dan membatalkan reservasi meja", async () => {
      // 1. Membuat meja baru
      const createResponse = await request(app)
        .post("/add/meja")
        .send({ tableNumber: 20, capacity: 4 });
  
      expect(createResponse.status).toBe(201);
      expect(createResponse.body.data).toHaveProperty("tableNumber", 20);
      expect(createResponse.body.data).toHaveProperty("status", "available");
  
      // 2. Memesan meja
      const reserveResponse = await request(app)
        .put("/meja/20/reserve")
        .send({ customerName: "John Doe" }); // Tambahkan customerName
  
      expect(reserveResponse.status).toBe(200);
      expect(reserveResponse.body.data).toHaveProperty("status", "reserved");
      expect(reserveResponse.body.data).toHaveProperty("customerName", "John Doe"); // Verifikasi customerName
  
      // 3. Membatalkan reservasi
      const cancelResponse = await request(app)
        .put("/meja/20/cancel")
        .send({});
  
      expect(cancelResponse.status).toBe(200);
      expect(cancelResponse.body.data).toHaveProperty("status", "available");
  
      // 4. Verifikasi status akhir
      const getResponse = await request(app).get("/meja");
  
      expect(getResponse.status).toBe(200);
      expect(getResponse.body.data.length).toBe(1);
      expect(getResponse.body.data[0]).toHaveProperty("tableNumber", 20);
      expect(getResponse.body.data[0]).toHaveProperty("status", "available");
    });
  });
});