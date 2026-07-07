"use client";
import React, { useEffect, useRef, useState } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { MapPin, Navigation, Loader2 } from "lucide-react";
import ubigeosData from "@/data/ubigeos_peru.json";

interface AddressMapSelectorProps {
  onSelectLocation: (data: {
    address: string;
    district: string;
    province: string;
    department: string;
    lat: number;
    lng: number;
  }) => void;
  initialLat?: number;
  initialLng?: number;
  selectedDepartment?: string;
  selectedProvince?: string;
  selectedDistrict?: string;
}

const cleanStr = (s?: string) => {
  if (!s) return "";
  return s
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "") // remover tildes y acentos
    .replace(/\b(provincia|distrito|departamento|region|región|de|del|la|las|el|los|metropolitan|province|district|city|town|village|state|county)\b/gi, "")
    .replace(/[^a-z0-9]/g, "")
    .trim();
};

const matchUbigeo = (addr: any, fallbackName?: string) => {
  const allDepts = Array.from(new Set(ubigeosData.map((u: any) => u.department)));
  const deptCandidates = [addr.state, addr.region, fallbackName, "Lima"].map(cleanStr).filter(Boolean);
  
  let matchedDept = "Lima";
  for (const d of allDepts) {
    const cd = cleanStr(d);
    if (deptCandidates.some(c => c === cd || c.includes(cd) || cd.includes(c))) {
      matchedDept = d;
      break;
    }
  }

  const provsForDept = Array.from(new Set(ubigeosData.filter((u: any) => u.department === matchedDept).map((u: any) => u.province)));
  const provCandidates = [addr.state_district, addr.county, addr.city, addr.region, addr.state, fallbackName].map(cleanStr).filter(Boolean);
  
  let matchedProv = provsForDept[0] || "Lima";
  for (const p of provsForDept) {
    const cp = cleanStr(p);
    if (provCandidates.some(c => c === cp || c.includes(cp) || cp.includes(c))) {
      matchedProv = p;
      break;
    }
  }

  const distsForProv = Array.from(new Set(ubigeosData.filter((u: any) => u.department === matchedDept && u.province === matchedProv).map((u: any) => u.district)));
  const distCandidates = [addr.suburb, addr.city_district, addr.town, addr.village, addr.quarter, addr.neighbourhood, addr.city, fallbackName].map(cleanStr).filter(Boolean);

  let matchedDist = distsForProv[0] || "Lima";
  for (const d of distsForProv) {
    const cd = cleanStr(d);
    if (distCandidates.some(c => c === cd || c.includes(cd) || cd.includes(c))) {
      matchedDist = d;
      break;
    }
  }

  return {
    department: matchedDept,
    province: matchedProv,
    district: matchedDist,
  };
};

const CITY_COORDS: Record<string, [number, number]> = {
  "chepen": [-7.2244, -79.4328],
  "trujillo": [-8.1159, -79.0299],
  "lima": [-12.0464, -77.0428],
  "limacentro": [-12.0464, -77.0428],
  "miraflores": [-12.1111, -77.0316],
  "sanisidro": [-12.0971, -77.0350],
  "surco": [-12.1450, -76.9900],
  "chiclayo": [-6.7714, -79.8409],
  "arequipa": [-16.4090, -71.5375],
  "piura": [-5.1945, -80.6328],
  "cusco": [-13.5319, -71.9675],
  "cajamarca": [-7.1638, -78.5003],
  "huancayo": [-12.0651, -75.2049],
  "iquitos": [-3.7491, -73.2538],
  "tacna": [-18.0056, -70.2483],
  "puno": [-15.8402, -70.0219],
  "chimbote": [-9.0853, -78.5783],
  "ica": [-14.0678, -75.7286],
  "pucallpa": [-8.3791, -74.5539],
  "pacasmayo": [-7.4006, -79.5714],
  "guadalupe": [-7.2483, -79.4719],
};

export function AddressMapSelector({
  onSelectLocation,
  initialLat = -12.0464, // Lima por defecto
  initialLng = -77.0428,
  selectedDepartment,
  selectedProvince,
  selectedDistrict,
}: AddressMapSelectorProps) {
  const mapContainerRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<L.Map | null>(null);
  const markerRef = useRef<L.Marker | null>(null);

  const [loadingGeo, setLoadingGeo] = useState(false);
  const [selectedText, setSelectedText] = useState<string>("Mueve el pin o haz clic en el mapa para seleccionar tu dirección exacta");

  const reverseGeocode = async (lat: number, lng: number, fallbackName?: string) => {
    setLoadingGeo(true);
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
      );
      let addr: any = {};
      let fullStreet = fallbackName || "Dirección en mapa";

      if (res.ok) {
        const data = await res.json();
        addr = data.address || {};
        const road = addr.road || addr.pedestrian || addr.suburb || addr.neighbourhood || "";
        const houseNumber = addr.house_number || "";
        fullStreet = `${road} ${houseNumber}`.trim() || data.display_name?.split(",")[0] || fallbackName || "Dirección en mapa";
      }

      const matched = matchUbigeo(addr, fallbackName);
      setSelectedText(`${fullStreet}, ${matched.district}`);
      onSelectLocation({
        address: fullStreet,
        district: matched.district,
        province: matched.province,
        department: matched.department,
        lat,
        lng,
      });
    } catch (err) {
      console.error("Error en geocodificación:", err);
      const matched = matchUbigeo({}, fallbackName);
      setSelectedText(`${fallbackName || "Ubicación"}, ${matched.district}`);
      onSelectLocation({
        address: fallbackName || "Ubicación seleccionada",
        district: matched.district,
        province: matched.province,
        department: matched.department,
        lat,
        lng,
      });
    } finally {
      setLoadingGeo(false);
    }
  };

  useEffect(() => {
    if (!mapContainerRef.current) return;
    if (mapInstanceRef.current) return; // Ya inicializado

    // Crear mapa
    const map = L.map(mapContainerRef.current, {
      center: [initialLat, initialLng],
      zoom: 14,
      zoomControl: true,
    });
    mapInstanceRef.current = map;

    // Capa OpenStreetMap
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19,
    }).addTo(map);

    // Icono personalizado de pin para evitar errores de Webpack con PNGs de Leaflet
    const customPinIcon = L.divIcon({
      className: "custom-map-marker",
      html: `<div style="font-size: 32px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); transform: translate(-50%, -100%); cursor: grab;">📍</div>`,
      iconSize: [32, 32],
      iconAnchor: [16, 32],
    });

    // Crear marcador arrastrable
    const marker = L.marker([initialLat, initialLng], {
      icon: customPinIcon,
      draggable: true,
    }).addTo(map);
    markerRef.current = marker;

    // Al arrastrar el marcador
    marker.on("dragend", () => {
      const pos = marker.getLatLng();
      reverseGeocode(pos.lat, pos.lng);
    });

    // Al hacer clic en cualquier parte del mapa
    map.on("click", (e: L.LeafletMouseEvent) => {
      marker.setLatLng(e.latlng);
      map.panTo(e.latlng);
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    // Geocodificación inicial
    reverseGeocode(initialLat, initialLng, selectedDistrict || selectedProvince);

    return () => {
      map.remove();
      mapInstanceRef.current = null;
    };
  }, []);

  // Sincronizar cuando el usuario cambia los selects de afuera
  useEffect(() => {
    if (!mapInstanceRef.current || !markerRef.current) return;
    const keyDist = cleanStr(selectedDistrict);
    const keyProv = cleanStr(selectedProvince);
    const keyDept = cleanStr(selectedDepartment);
    
    const coords = CITY_COORDS[keyDist] || CITY_COORDS[keyProv] || CITY_COORDS[keyDept];
    if (coords) {
      const currentPos = markerRef.current.getLatLng();
      // Solo mover si está razonablemente lejos para evitar bucles infinitos
      if (Math.abs(currentPos.lat - coords[0]) > 0.01 || Math.abs(currentPos.lng - coords[1]) > 0.01) {
        const newLatLng = new L.LatLng(coords[0], coords[1]);
        markerRef.current.setLatLng(newLatLng);
        mapInstanceRef.current.setView(newLatLng, 15);
        reverseGeocode(coords[0], coords[1], selectedDistrict || selectedProvince);
      }
    }
  }, [selectedDistrict, selectedProvince, selectedDepartment]);

  const handleUseGPS = () => {
    if (!navigator.geolocation) {
      alert("Tu navegador no soporta geolocalización");
      return;
    }
    setLoadingGeo(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const { latitude, longitude } = pos.coords;
        if (mapInstanceRef.current && markerRef.current) {
          const newLatLng = new L.LatLng(latitude, longitude);
          markerRef.current.setLatLng(newLatLng);
          mapInstanceRef.current.setView(newLatLng, 16);
          reverseGeocode(latitude, longitude);
        }
      },
      (err) => {
        setLoadingGeo(false);
        alert("No se pudo obtener tu ubicación GPS. Verifica los permisos de tu navegador.");
      },
      { enableHighAccuracy: true }
    );
  };

  const handleQuickCity = (lat: number, lng: number, name: string) => {
    if (mapInstanceRef.current && markerRef.current) {
      const newLatLng = new L.LatLng(lat, lng);
      markerRef.current.setLatLng(newLatLng);
      mapInstanceRef.current.setView(newLatLng, 15);
      reverseGeocode(lat, lng, name);
    }
  };

  return (
    <div className="space-y-2.5 my-3 bg-muted/30 p-3 rounded-2xl border border-border">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <span className="text-xs font-bold text-foreground flex items-center gap-1.5">
          <MapPin className="w-4 h-4 text-primary" />
          Selecciona tu ubicación en el mapa:
        </span>
        <button
          type="button"
          onClick={handleUseGPS}
          className="text-xs bg-primary text-primary-foreground px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm hover:opacity-90 transition-opacity"
        >
          <Navigation className="w-3.5 h-3.5" />
          Usar mi GPS actual
        </button>
      </div>

      {/* Botones rápidos de ciudades/zonas populares */}
      <div className="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px]">
        <span className="text-muted-foreground font-medium shrink-0">Zonas rápidas:</span>
        <button
          type="button"
          onClick={() => handleQuickCity(-7.2244, -79.4328, "Chepen")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Chepén
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-8.1159, -79.0299, "Trujillo")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Trujillo
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-12.0464, -77.0428, "Lima")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Lima Centro
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-6.7714, -79.8409, "Chiclayo")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Chiclayo
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-16.409, -71.5375, "Arequipa")}
          className="px-2.5 py-1 bg-background hover:bg-muted border rounded-lg font-semibold transition-colors shrink-0"
        >
          Arequipa
        </button>
      </div>

      {/* Contenedor del Mapa */}
      <div
        ref={mapContainerRef}
        className="w-full h-52 sm:h-60 rounded-xl overflow-hidden border-2 border-primary/20 shadow-inner z-10 relative bg-zinc-100 dark:bg-zinc-800"
      />

      {/* Barra de estado / Dirección detectada */}
      <div className="flex items-center gap-2 bg-background p-2.5 rounded-xl border text-xs">
        {loadingGeo ? (
          <Loader2 className="w-4 h-4 text-primary animate-spin shrink-0" />
        ) : (
          <span className="text-emerald-600 dark:text-emerald-400 font-bold shrink-0">📍 Detectado:</span>
        )}
        <span className="font-medium text-foreground truncate flex-1">
          {loadingGeo ? "Buscando nombre de la calle y distrito..." : selectedText}
        </span>
      </div>
    </div>
  );
}
