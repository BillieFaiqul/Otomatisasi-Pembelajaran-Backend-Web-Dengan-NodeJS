const request = require('supertest');
const mongoose = require('mongoose');
const {app} = require('../app'); 
const packages = require('../package.json');
const connectDB = require('../src/config/database');



describe("Pengujian konfigurasi aplikasi", () => {
  it("Harus memiliki paket-paket development yang diperlukan", (done) => {
    try {
      expect(packages.devDependencies).toHaveProperty("jest");
    } catch (error) {
      throw new Error('Paket "jest" tidak ditemukan di devDependencies. Jalankan "npm i jest --save-dev".');
    }
    
    try {
      expect(packages.devDependencies).toHaveProperty("nodemon");
    } catch (error) {
      throw new Error('Paket "nodemon" tidak ditemukan di devDependencies. Jalankan "npm i nodemon --save-dev".');
    }
    
    try {
      expect(packages.devDependencies).toHaveProperty("supertest");
    } catch (error) {
      throw new Error('Paket "supertest" tidak ditemukan di devDependencies. Jalankan "npm i supertest --save-dev".');
    }
   
    try {
      expect(packages.devDependencies).toHaveProperty("cross-env");
    } catch (error) {
      throw new Error('Paket "cross-env" tidak ditemukan di devDependencies. Jalankan "npm i cross-env --save-dev".');
    }
    done();
  });

  it("Harus memiliki paket-paket production yang diperlukan", (done) => {
    try {
      expect(packages.dependencies).toHaveProperty("dotenv");
    } catch (error) {
      throw new Error('Paket "dotenv" tidak ditemukan di dependencies. Jalankan "npm i dotenv --save".');
    }
    
    try {
      expect(packages.dependencies).toHaveProperty("express");
    } catch (error) {
      throw new Error('Paket "express" tidak ditemukan di dependencies. Jalankan "npm i express --save".');
    }
    
    try {
      expect(packages.dependencies).toHaveProperty("mongoose");
    } catch (error) {
      throw new Error('Paket "mongoose" tidak ditemukan di dependencies. Jalankan "npm i mongoose --save".');
    }
    done();
  });

  it("Harus memiliki nama yang benar", (done) => {
    try {
      expect(packages.name).toBe("restaurant-reservation");
    } catch (error) {
      throw new Error(`Nama aplikasi harus "restaurant-reservation", tetapi ditemukan "${packages.name}".`);
    }
    done();
  });

  it("Harus memiliki variabel lingkungan yang benar", (done) => {
    try {
      expect(process.env).toHaveProperty("MONGODB_URL");
    } catch (error) {
      throw new Error('Variabel lingkungan "MONGODB_URL" tidak ditemukan. Periksa file .env');
    }
    
    try {
      expect(process.env).toHaveProperty("PORT");
    } catch (error) {
      throw new Error('Variabel lingkungan "PORT" tidak ditemukan. Periksa file .env');
    }
   
    try {
        expect(process.env).toHaveProperty("MONGODB_URL_TEST");
      } catch (error) {
        throw new Error('Variabel lingkungan "MONGODB_URL_TEST" tidak ditemukan. Periksa file .env');
    }

    try {
      expect(process.env).toHaveProperty("NODE_ENV");
    } catch (error) {
      throw new Error('Variabel lingkungan "NODE_ENV" tidak ditemukan. Periksa file .env');
  }
    done();
  });
});

  describe("Pengujian Middleware Aplikasi", () => {
      it("Harus memiliki middleware yang diperlukan", (done) => {
        let application_stack = [];
        app._router.stack.forEach((element) => {
          application_stack.push(element.name);
        });
    
        // Test for JSON middleware
        expect(application_stack).toContain("jsonParser");
        if (!application_stack.includes("jsonParser")) {
          throw new Error("Aplikasi tidak menggunakan format JSON. Periksa file app.js");
        }
    
        // Test for Express middleware
        expect(application_stack).toContain("expressInit");
        if (!application_stack.includes("expressInit")) {
          throw new Error("Aplikasi tidak menggunakan express framework. Periksa file app.js");
        }
    
        // Test for URL-encoded middleware
        expect(application_stack).toContain("urlencodedParser");
        if (!application_stack.includes("urlencodedParser")) {
          throw new Error("Aplikasi tidak menggunakan format urlencoded. Periksa file app.js");
        }
    
        done();
      });
    });

describe('Pengujian Koneksi Database', () => {
    it('Harus berhasil terhubung ke database MongoDB', async () => {
      try {
        const state = mongoose.connection.readyState; 
        expect(state).toBe(1);
      } catch (error) {
        throw new Error(`Gagal terhubung ke database: ${error.message}`);
      }
    });
});

describe('Pengujian API Utama', () => {
  it('Harus mengembalikan pesan yang sesuai', async () => {
    try {
      const res = await request(app).get('/test');
      expect(res.statusCode).toBe(200);
      expect(res.body).toHaveProperty('status', 'success');
      expect(res.body).toHaveProperty('message', 'Welcome to Restaurant Reservation API');
      expect(res.body).toHaveProperty('version', '1.0.0');
    } catch (error) {
      throw new Error(`Terjadi kesalahan pada pengujian GET /: ${error.message}`);
    }
  });
});

  
async function disconnectDB() {
  await mongoose.connection.close();
}

beforeAll(async () => {
    try {
      await connectDB();
      dbConnected = true;
    } catch (error) {
      console.warn('⚠️  Gagal terhubung ke database:', error.message);
    }
  });
  

afterAll(async () => {
  await disconnectDB();
});
