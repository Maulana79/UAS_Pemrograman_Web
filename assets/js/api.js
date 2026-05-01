// assets/js/api.js

// Ganti dengan URL website InfinityFree kamu yang asli!
// Jangan pakai localhost, jangan pakai < >
// ✅ INI BENAR (HTTPS dan Domain Asli Kamu)
const BASE_API_URL = "https://turipuloka.42web.io/api";

const API_KEY = "janginam777"; // Sesuaikan dengan .env / config.php

function getHeaders() {
    const headers = {
        "Content-Type": "application/json",
        "X-Api-Key": API_KEY
    };
    
    // Pastikan key-nya 'auth_token' (Sesuai dengan login.html)
    const token = localStorage.getItem("auth_token"); 
    
    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }
    return headers;
}

function formatRupiah(angka) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0
    }).format(angka);
}