const mongoose = require("mongoose");
const request = require("supertest");
const { app } = require("../../app");
const connectDB = require("../../src/config/database");

describe("Module 1 - Database & Basic API", () => {
  beforeAll(async () => {
    jest.spyOn(console, 'log').mockImplementation(() => { });

    await connectDB();
  });

  afterAll(async () => {
    await mongoose.connection.close();
  });

  describe("Database Connection", () => {
    test("harus berhasil terhubung dengan MongoDB", () => {
      const connectionState = mongoose.connection.readyState;
      expect(connectionState).toBe(1);
    });
  });

  describe("Test API Route", () => {
    test("harus mengembalikan format respons yang benar dari rute pengujian", async () => {
      const response = await request(app).get("/test");

      expect(response.status).toBe(200);
      expect(response.body).toEqual({
        message: "Welcome to Restaurant Reservation API",
      });
    });
  });
});
