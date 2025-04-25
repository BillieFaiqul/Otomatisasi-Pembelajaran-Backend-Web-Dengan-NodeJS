const errorHandler = require("../../src/middleware/errorHandler");
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

describe("Modul 5 - Error Handling", () => {
  let req, res, next;

  beforeEach(() => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.clearAllMocks();

    console.error = jest.fn();

    req = {
      body: {
        tableNumber: 7,
        items: [{ menuId: mockObjectId.toString(), quantity: 2 }],
      },
      params: {},
    };

    res = mockResponse();
    next = jest.fn();
  });

  describe("Error Handler Middleware", () => {
    test("harus menangani error dengan status code dari error", () => {
      const error = new Error("Validation error");
      error.statusCode = 400;

      errorHandler(error, req, res, next);

      expect(console.error).toHaveBeenCalled();
      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Validation error",
      });
    });

    test("harus menangani error tanpa status code dengan default 500", () => {
      const error = new Error("Server error");

      errorHandler(error, req, res, next);

      expect(console.error).toHaveBeenCalled();
      expect(res.status).toHaveBeenCalledWith(500);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Server error",
      });
    });
  });

  describe("Order Controller Error Handling", () => {
    test("harus menangani error meja tidak tersedia dengan status 400", async () => {
      Meja.findOne.mockResolvedValue(null);

      await orderController.createOrder(req, res, next);

      expect(next).toHaveBeenCalledWith(expect.any(Error));
      expect(next.mock.calls[0][0].message).toBe(
        "Meja tidak tersedia atau sedang dipesan",
      );
      expect(next.mock.calls[0][0].statusCode).toBe(400);
    });

    test("harus menangani error menu tidak valid dengan status 400", async () => {
      Meja.findOne.mockResolvedValue({ tableNumber: 7, status: "available" });
      Menu.find.mockResolvedValue([]);

      await orderController.createOrder(req, res, next);

      expect(next).toHaveBeenCalledWith(expect.any(Error));
      expect(next.mock.calls[0][0].message).toBe(
        "Beberapa item menu tidak valid",
      );
      expect(next.mock.calls[0][0].statusCode).toBe(400);
    });

    test("harus menangani exception dengan middleware error", async () => {
      const errorMessage = "Database connection failed";
      Meja.findOne.mockImplementation(() => {
        throw new Error(errorMessage);
      });

      await orderController.createOrder(req, res, next);

      expect(next).toHaveBeenCalledWith(expect.any(Error));
      expect(next.mock.calls[0][0].message).toBe(errorMessage);
    });

    test("harus mengubah error menjadi format error response", () => {
      const error = new Error("Custom error");
      error.statusCode = 422;

      errorHandler(error, req, res, next);

      expect(res.status).toHaveBeenCalledWith(422);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Custom error",
      });
    });
  });

  describe("Custom Error Creation", () => {
    test("harus membuat error dengan statusCode yang tepat", async () => {
      const createError = (message, statusCode) => {
        const error = new Error(message);
        error.statusCode = statusCode;
        return error;
      };

      const error = createError("Resource not found", 404);

      expect(error).toBeInstanceOf(Error);
      expect(error.message).toBe("Resource not found");
      expect(error.statusCode).toBe(404);
    });
  });
});
