const mongoose = require("mongoose");
const request = require("supertest");
const { app } = require("../../app");
const Menu = require("../../src/models/menuModel");
const connectDB = require("../../src/config/database");

describe("Module 2 - Menu API", () => {
  beforeAll(async () => {
    jest.spyOn(console, 'log').mockImplementation(() => { });

    await connectDB();
  });

  beforeEach(async () => {
    await Menu.deleteMany({});
  });

  afterAll(async () => {
    await mongoose.connection.close();
  });

  describe("POST /createMenu", () => {
    test("harus berhasil membuat item menu baru", async () => {
      const menuItem = {
        name: "Nasi Goreng",
        description: "Nasi goreng dengan telur mata sapi dan ayam kampung",
        price: 25000,
        category: "main",
        isAvailable: true,
      };

      const response = await request(app).post("/createMenu").send(menuItem);

      expect(response.status).toBe(201);
      expect(response.body).toHaveProperty("name", "Nasi Goreng");
      expect(response.body).toHaveProperty("price", 25000);
      expect(response.body).toHaveProperty("category", "main");
    });
  });

  describe("GET /menu", () => {
    test("harus mengembalikan seluruh item menu", async () => {
      const firstItem = await Menu.create({
        name: "Nasi Putih",
        price: 5000,
        category: "main",
        isAvailable: true,
      });
      const secondItem = await Menu.create({
        name: "Kerupuk Udang",
        price: 3000,
        category: "appetizer",
        isAvailable: true,
      });

      const response = await request(app).get("/menu");

      expect(response.status).toBe(200);
      expect(response.body.length).toBeGreaterThanOrEqual(2);
      const nasiInResponse = response.body.find(
        (item) => item._id === firstItem._id.toString(),
      );
      const kerupukInResponse = response.body.find(
        (item) => item._id === secondItem._id.toString(),
      );

      expect(nasiInResponse).toBeDefined();
      expect(kerupukInResponse).toBeDefined();
      expect(nasiInResponse).toHaveProperty("name", "Nasi Putih");
      expect(kerupukInResponse).toHaveProperty("name", "Kerupuk Udang");
    });

    test("harus mengembalikan array kosong jika tidak ada item menu", async () => {
      await Menu.deleteMany({});

      const response = await request(app).get("/menu");

      expect(response.status).toBe(200);
      expect(Array.isArray(response.body)).toBe(true);
      expect(response.body.length).toBe(0);
    });
  });

  describe("GET /menu/:category", () => {
    test("harus mengembalikan item menu berdasarkan kategori yang ditentukan", async () => {
      await Menu.deleteMany({});

      await Menu.create([
        {
          name: "Nasi Rames",
          price: 20000,
          category: "main",
          isAvailable: true,
        },
        {
          name: "Gado-gado",
          price: 15000,
          category: "appetizer",
          isAvailable: true,
        },
        {
          name: "Soto Ayam",
          price: 18000,
          category: "main",
          isAvailable: true,
        },
      ]);

      const response = await request(app).get("/menu/main");

      expect(response.status).toBe(200);
      expect(Array.isArray(response.body)).toBe(true);
      expect(response.body.length).toBe(2);

      response.body.forEach((item) => {
        expect(item.category).toBe("main");
      });
    });

    test("harus mengembalikan status 404 jika kategori tidak ditemukan", async () => {
      const response = await request(app).get("/menu/bukanmakanan");

      expect(response.status).toBe(404);
      expect(response.body).toHaveProperty(
        "error",
        "Menu with category 'bukanmakanan' not found",
      );
    });
  });
});
