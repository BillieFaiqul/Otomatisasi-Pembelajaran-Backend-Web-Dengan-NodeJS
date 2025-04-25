const mongoose = require("mongoose");
const request = require("supertest");
const { app } = require("../app");
const Menu = require("../src/models/menuModel");
const connectDB = require("../src/config/database");

describe("Pengujian Integrasi - API Menu", () => {
  beforeAll(async () => {
    await connectDB();
    await Menu.deleteMany({}); // Pastikan database dalam keadaan kosong sebelum pengujian
  });

  beforeEach(async () => {
    await Menu.deleteMany({}); // Hapus semua data sebelum setiap pengujian
  });

  afterAll(async () => {
    await mongoose.connection.close(); // Tutup koneksi database setelah semua pengujian selesai
  });

  it("harus berhasil membuat item menu baru melalui API", async () => {
    const newItem = {
      name: "Pizza",
      description: "Pizza keju yang lezat",
      price: 12.99,
      category: "main",
      isAvailable: true,
    };

    const res = await request(app).post("/createMenu").send(newItem);

    expect(res.status).toBe(201);
    expect(res.body.name).toBe(newItem.name);
    expect(res.body.price).toBe(newItem.price);
  });

  it("harus mengambil semua item menu melalui API", async () => {
    await Menu.create([
      { name: "Burger", price: 9.99, category: "main", isAvailable: true },
      { name: "Salad", price: 5.99, category: "appetizer", isAvailable: true },
    ]);

    const res = await request(app).get("/menu");

    expect(res.status).toBe(200);
    expect(res.body.length).toBe(2);
  });

  it("harus mengambil item menu berdasarkan kategori melalui API", async () => {
    await Menu.create([
      { name: "Steak", price: 19.99, category: "main", isAvailable: true },
      { name: "Soda", price: 2.99, category: "beverage", isAvailable: true },
    ]);

    const res = await request(app).get("/menu/main");

    expect(res.status).toBe(200);
    expect(res.body.length).toBe(1);
    expect(res.body[0].name).toBe("Steak");
  });

  it("harus mengembalikan 404 jika kategori tidak ditemukan", async () => {
    const res = await request(app).get("/menu/dessert");

    expect(res.status).toBe(404);
    expect(res.body.error).toBe("Menu with category 'dessert' not found");
  });
});
