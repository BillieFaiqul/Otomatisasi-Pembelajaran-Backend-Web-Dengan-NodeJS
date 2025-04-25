const Menu = require("../src/models/menuModel");
const menuController = require("../src/controllers/menuController");

// Mock model Menu
jest.mock("../src/models/menuModel");

describe("Pengujian Unit - Controller Menu", () => {
  let req, res;

  beforeEach(() => {
    jest.clearAllMocks();

    req = {
      body: {
        name: "Item Uji",
        description: "Deskripsi Uji",
        price: 9.99,
        category: "main",
        isAvailable: true,
      },
      params: {
        category: "main",
      },
    };

    res = {
      status: jest.fn().mockReturnThis(),
      json: jest.fn(),
    };
  });

  // ✅ Pengujian Pembuatan Item Menu
  describe("createMenuItem", () => {
    it("harus berhasil membuat item menu", async () => {
      const savedItem = { ...req.body, _id: "123" };
      Menu.mockImplementation(() => ({
        save: jest.fn().mockResolvedValue(savedItem),
      }));

      await menuController.createMenuItem(req, res);

      expect(res.status).toHaveBeenCalledWith(201);
      expect(res.json).toHaveBeenCalledWith(savedItem);
    });
  });

  // ✅ Pengujian Mendapatkan Semua Item Menu
  describe("getAllMenuItems", () => {
    it("harus mengembalikan semua item menu dengan sukses", async () => {
      const items = [
        { name: "Item 1", price: 9.99 },
        { name: "Item 2", price: 14.99 },
      ];
      Menu.find.mockResolvedValue(items);

      await menuController.getAllMenuItems(req, res);

      expect(Menu.find).toHaveBeenCalledWith({});
      expect(res.json).toHaveBeenCalledWith(items);
    });
  });

  // ✅ Pengujian Mendapatkan Menu Berdasarkan Kategori
  describe("getMenuByCategory", () => {
    it("harus mengembalikan item untuk kategori yang valid", async () => {
      const items = [
        { name: "Main Course 1", category: "main" },
        { name: "Main Course 2", category: "main" },
      ];
      Menu.find.mockResolvedValue(items);

      await menuController.getMenuByCategory(req, res);

      expect(Menu.find).toHaveBeenCalledWith({ category: "main" });
      expect(res.json).toHaveBeenCalledWith(items);
    });

    it("harus menangani kategori yang tidak ditemukan", async () => {
      Menu.find.mockResolvedValue([]);

      await menuController.getMenuByCategory(req, res);

      expect(res.status).toHaveBeenCalledWith(404);
      expect(res.json).toHaveBeenCalledWith({
        error: "Menu with category 'main' not found",
      });
    });
  });
});
