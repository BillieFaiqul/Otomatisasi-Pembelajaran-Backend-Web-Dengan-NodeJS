const mongoose = require('mongoose');
const Meja = require('../src/models/mejaModel');
const mejaController = require('../src/controllers/mejaController');

// Mock Express req dan res objects
const mockRequest = (body = {}, params = {}) => ({
  body,
  params
});

const mockResponse = () => {
  const res = {};
  res.status = jest.fn().mockReturnValue(res);
  res.json = jest.fn().mockReturnValue(res);
  return res;
};

// Mock the Meja model methods
jest.mock('../src/models/mejaModel');

describe('Meja Controller', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('createMeja', () => {
    test('harus membuat meja baru dan mengembalikan status 201', async () => {
      // Arrange
      const mockMeja = { 
        tableNumber: 1, 
        capacity: 4, 
        status: 'available' 
      };
      
      Meja.create.mockResolvedValue(mockMeja);
      
      const req = mockRequest({ tableNumber: 1, capacity: 4 });
      const res = mockResponse();
      
      // Act
      await mejaController.createMeja(req, res);
      
      // Assert
      expect(Meja.create).toHaveBeenCalledWith({ 
        tableNumber: 1, 
        capacity: 4 
      });
      expect(res.status).toHaveBeenCalledWith(201);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockMeja
      });
    });
    
    test('harus menangani error dan mengembalikan status 400', async () => {
      // Arrange
      const errorMessage = 'Validation error';
      Meja.create.mockRejectedValue(new Error(errorMessage));
      
      const req = mockRequest({ tableNumber: 1, capacity: 4 });
      const res = mockResponse();
      
      // Act
      await mejaController.createMeja(req, res);
      
      // Assert
      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage
      });
    });
  });
  
  describe('getAllMeja', () => {
    test('harus mengembalikan semua meja dengan status 200', async () => {
      // Arrange
      const mockMejaList = [
        { tableNumber: 1, capacity: 4, status: 'available' },
        { tableNumber: 2, capacity: 2, status: 'reserved' }
      ];
      
      // Setup chaining untuk find().sort()
      const mockSort = jest.fn().mockResolvedValue(mockMejaList);
      const mockFind = jest.fn().mockReturnValue({ sort: mockSort });
      Meja.find = mockFind;
      
      const req = mockRequest();
      const res = mockResponse();
      
      // Act
      await mejaController.getAllMeja(req, res);
      
      // Assert
      expect(Meja.find).toHaveBeenCalled();
      expect(mockSort).toHaveBeenCalledWith({ tableNumber: 1 });
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockMejaList
      });
    });
    
    test('harus menangani error dan mengembalikan status 400', async () => {
      // Arrange
      const errorMessage = 'Database error';
      
      // Setup chaining untuk find().sort() yang mengembalikan error
      const mockSort = jest.fn().mockRejectedValue(new Error(errorMessage));
      const mockFind = jest.fn().mockReturnValue({ sort: mockSort });
      Meja.find = mockFind;
      
      const req = mockRequest();
      const res = mockResponse();
      
      // Act
      await mejaController.getAllMeja(req, res);
      
      // Assert
      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage
      });
    });
  });
  
  describe('reserveMeja', () => {
    test('harus memesan meja yang tersedia dan mengembalikan status 200', async () => {
      // Arrange
      const tableNumber = '5';
      const customerName = 'John Doe';
      const mockMeja = { 
        tableNumber: 5, 
        capacity: 4, 
        status: 'reserved',
        customerName: 'John Doe'
      };
      
      Meja.findOneAndUpdate.mockResolvedValue(mockMeja);
      
      const req = mockRequest({ customerName }, { tableNumber });
      const res = mockResponse();
      
      // Act
      await mejaController.reserveMeja(req, res);
      
      // Assert
      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber, status: 'available' },
        { status: 'reserved', customerName },
        { new: true }
      );
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockMeja
      });
    });
    
    test('harus mengembalikan 404 ketika meja tidak tersedia', async () => {
      // Arrange
      const tableNumber = '5';
      const customerName = 'John Doe';
      
      Meja.findOneAndUpdate.mockResolvedValue(null);
      
      const req = mockRequest({ customerName }, { tableNumber });
      const res = mockResponse();
      
      // Act
      await mejaController.reserveMeja(req, res);
      
      // Assert
      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber, status: 'available' },
        { status: 'reserved', customerName },
        { new: true }
      );
      expect(res.status).toHaveBeenCalledWith(404);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: 'Meja tidak tersedia'
      });
    });
    
    test('harus mengembalikan 400 ketika customerName tidak disediakan', async () => {
      // Arrange
      const tableNumber = '5';
      
      const req = mockRequest({ tableNumber }, {});
      const res = mockResponse();
      
      // Act
      await mejaController.reserveMeja(req, res);
      
      // Assert
      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: 'Nama pelanggan harus diisi'
      });
    });
    
    test('harus menangani error dan mengembalikan status 400', async () => {
      // Arrange
      const tableNumber = '5';
      const customerName = 'John Doe';
      const errorMessage = 'Database error';
      
      Meja.findOneAndUpdate.mockRejectedValue(new Error(errorMessage));
      
      const req = mockRequest({ customerName }, { tableNumber });
      const res = mockResponse();
      
      // Act
      await mejaController.reserveMeja(req, res);
      
      // Assert
      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber, status: 'available' },
        { status: 'reserved', customerName },
        { new: true }
      );
      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage
      });
    });
  });
  
  describe('cancelReservation', () => {
    test('harus membatalkan reservasi dan mengembalikan status 200', async () => {
      // Arrange
      const tableNumber = '3';
      const mockMeja = { 
        tableNumber: 3, 
        capacity: 4, 
        status: 'available',
        customerName: ''
      };
      
      Meja.findOneAndUpdate.mockResolvedValue(mockMeja);
      
      const req = mockRequest({}, { tableNumber });
      const res = mockResponse();
      
      // Act
      await mejaController.cancelReservation(req, res);
      
      // Assert
      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber, status: 'reserved' },
        { status: 'available', customerName: '', updatedAt: expect.any(Number) },
        { new: true }
      );
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        message: `Reservation for table ${tableNumber} has been cancelled`,
        data: mockMeja
      });
    });
    
    test('harus mengembalikan 404 ketika meja tidak ditemukan atau tidak sedang dipesan', async () => {
      // Arrange
      const tableNumber = '3';
      
      Meja.findOneAndUpdate.mockResolvedValue(null);
      
      const req = mockRequest({}, { tableNumber });
      const res = mockResponse();
      
      // Act
      await mejaController.cancelReservation(req, res);
      
      // Assert
      expect(res.status).toHaveBeenCalledWith(404);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: 'Table not found or not currently reserved'
      });
    });
    
    test('harus menangani error dan mengembalikan status 400', async () => {
      // Arrange
      const tableNumber = '3';
      const errorMessage = 'Database error';
      
      Meja.findOneAndUpdate.mockRejectedValue(new Error(errorMessage));
      
      const req = mockRequest({}, { tableNumber });
      const res = mockResponse();
      
      // Act
      await mejaController.cancelReservation(req, res);
      
      // Assert
      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage
      });
    });
  });
});