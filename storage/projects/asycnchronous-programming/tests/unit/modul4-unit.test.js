const mongoose = require("mongoose");
const Order = require("../../src/models/orderModel");
const Meja = require("../../src/models/mejaModel");
const Menu = require("../../src/models/menuModel");
const orderController = require("../../src/controllers/orderController");

const mockRequest = (body = {}, params = {}) => ({
  body,
  params,
});

const mockResponse = () => {
  const res = {};
  res.status = jest.fn().mockReturnValue(res);
  res.json = jest.fn().mockReturnValue(res);
  return res;
};

jest.mock("../../src/models/orderModel");
jest.mock("../../src/models/mejaModel");
jest.mock("../../src/models/menuModel");

const mockObjectId = new mongoose.Types.ObjectId();

describe("Modul 4 - Order Controller", () => {
  let req, res, next;

  beforeEach(() => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.clearAllMocks();

    req = {
      body: {
        tableNumber: 3,
        items: [
          { menuId: mockObjectId.toString(), quantity: 1 },
          { menuId: mockObjectId.toString(), quantity: 2 },
        ],
      },
      params: {
        orderId: mockObjectId.toString(),
      },
    };

    res = mockResponse();

    next = jest.fn();
  });

  describe("createOrder", () => {
    test("harus berhasil membuat pesanan baru", async () => {
      const mockMeja = {
        tableNumber: 3,
        status: "available",
      };

      const mockMenuItems = [
        { _id: mockObjectId, price: 25000 },
        { _id: mockObjectId, price: 20000 },
      ];

      const mockSavedOrder = {
        _id: mockObjectId,
        tableNumber: 3,
        items: req.body.items,
        total: 65000,
        status: "pending",
      };

      Meja.findOne.mockResolvedValue(mockMeja);
      Menu.find.mockResolvedValue(mockMenuItems);
      Order.prototype.save = jest.fn().mockResolvedValue(mockSavedOrder);
      Meja.findOneAndUpdate.mockResolvedValue({ status: "reserved" });

      await orderController.createOrder(req, res, next);

      expect(Meja.findOne).toHaveBeenCalledWith({
        tableNumber: 3,
        status: "available",
      });

      expect(Menu.find).toHaveBeenCalled();
      expect(Order.prototype.save).toHaveBeenCalled();
      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber: 3 },
        { status: "reserved" },
      );

      expect(res.status).toHaveBeenCalledWith(201);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockSavedOrder,
      });

    });

    test("harus menangani error ketika meja tidak tersedia", async () => {
      Meja.findOne.mockResolvedValue(null);

      await orderController.createOrder(req, res, next);

      expect(Meja.findOne).toHaveBeenCalledWith({
        tableNumber: 3,
        status: "available",
      });
    });

    test("harus menangani error ketika item menu tidak valid", async () => {
      Meja.findOne.mockResolvedValue({ tableNumber: 3, status: "available" });
      Menu.find.mockResolvedValue([]);

      await orderController.createOrder(req, res, next);

      expect(Menu.find).toHaveBeenCalled();
    });
  });

  describe("getAllOrders", () => {
    test("harus mengembalikan semua pesanan", async () => {
      const mockOrders = [
        { _id: mockObjectId, tableNumber: 1, total: 50000, status: "pending" },
        {
          _id: mockObjectId,
          tableNumber: 2,
          total: 75000,
          status: "completed",
        },
      ];

      const mockSort = jest.fn().mockResolvedValue(mockOrders);
      Order.find = jest.fn().mockReturnValue({ sort: mockSort });

      await orderController.getAllOrders(req, res);

      expect(Order.find).toHaveBeenCalled();
      expect(mockSort).toHaveBeenCalledWith({ createdAt: -1 });
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockOrders,
      });
    });

    test("harus menangani error saat mengambil pesanan", async () => {
      const errorMessage = "Database error";
      Order.find = jest.fn().mockImplementation(() => {
        throw new Error(errorMessage);
      });

      await orderController.getAllOrders(req, res);

      expect(Order.find).toHaveBeenCalled();
      expect(res.status).toHaveBeenCalledWith(500);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage,
      });
    });
  });

  describe("updateOrderStatus", () => {
    test("harus berhasil mengupdate status pesanan", async () => {
      req.body = { status: "completed" };

      const mockUpdatedOrder = {
        _id: mockObjectId,
        tableNumber: 3,
        status: "completed",
      };

      Order.findByIdAndUpdate.mockResolvedValue(mockUpdatedOrder);
      Meja.findOneAndUpdate.mockResolvedValue({ status: "available" });

      await orderController.updateOrderStatus(req, res);

      expect(Order.findByIdAndUpdate).toHaveBeenCalledWith(
        mockObjectId.toString(),
        { status: "completed" },
        { new: true },
      );
      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber: 3 },
        { status: "available" },
      );
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockUpdatedOrder,
      });
    });

    test("harus mengembalikan 404 ketika pesanan tidak ditemukan", async () => {
      req.body = { status: "completed" };

      Order.findByIdAndUpdate.mockResolvedValue(null);

      await orderController.updateOrderStatus(req, res);

      expect(Order.findByIdAndUpdate).toHaveBeenCalledWith(
        mockObjectId.toString(),
        { status: "completed" },
        { new: true },
      );
      expect(res.status).toHaveBeenCalledWith(404);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Pesanan tidak ditemukan",
      });
    });

    test("harus menangani error saat mengupdate status", async () => {
      req.body = { status: "completed" };

      const errorMessage = "Database error";
      Order.findByIdAndUpdate.mockRejectedValue(new Error(errorMessage));

      await orderController.updateOrderStatus(req, res);

      expect(Order.findByIdAndUpdate).toHaveBeenCalled();
      expect(res.status).toHaveBeenCalledWith(500);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage,
      });
    });
  });
});
