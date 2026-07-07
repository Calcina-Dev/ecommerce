"use client";
import React, { useEffect, useRef, useState } from "react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { MapPin, Navigation, Loader2, Search, X, CheckCircle2 } from "lucide-react";
import { toast } from "sonner";
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

const searchNominatimRobust = async (rawQ: string, city?: string) => {
  if (!rawQ || !rawQ.trim()) return [];
  const q = rawQ.trim();
  
  // 1. Expandir abreviaturas peruanas comunes
  let expanded = q
    .replace(/\bjr\.?\b/gi, "Jirón")
    .replace(/\bav\.?\b/gi, "Avenida")
    .replace(/\bcl\.?\b/gi, "Calle")
    .replace(/\bpsj\.?\b/gi, "Pasaje")
    .replace(/\burb\.?\b/gi, "Urbanización")
    .replace(/\bmz\.?\b/gi, "Manzana")
    .replace(/\blt\.?\b/gi, "Lote")
    .replace(/\bdpto\.?\b/gi, "");

  // 2. Remover prefijo (jr, av, calle, jiron, etc.) por si en OSM la calle solo se llama "2 de Mayo" o "Grau"
  let noPrefix = q
    .replace(/^(jr\.?|jiron|jirón|av\.?|avenida|cl\.?|calle|psj\.?|pasaje|urb\.?|urbanizacion|urbanización)\s+/i, "")
    .trim();

  const cityPart = city && city !== "Lima" && !q.toLowerCase().includes(city.toLowerCase()) ? `, ${city}` : "";
  
  const queriesToTry = [
    `${expanded}${cityPart}, Peru`,
    `${q}${cityPart}, Peru`,
    noPrefix && noPrefix !== q ? `${noPrefix}${cityPart}, Peru` : "",
    `${expanded}, Peru`,
    `${q}, Peru`,
    noPrefix && noPrefix !== q ? `${noPrefix}, Peru` : "",
  ].filter(Boolean);

  const uniqueQueries = Array.from(new Set(queriesToTry));

  for (const queryStr of uniqueQueries) {
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(queryStr)}&countrycodes=pe&addressdetails=1&limit=5`
      );
      if (res.ok) {
        const data = await res.json();
        if (data && Array.isArray(data) && data.length > 0) {
          return data;
        }
      }
    } catch (err) {
      console.error("Error intentando query:", queryStr, err);
    }
  }
  return [];
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
  const searchRef = useRef<HTMLDivElement>(null);

  const [loadingGeo, setLoadingGeo] = useState(false);
  const [selectedText, setSelectedText] = useState<string>("Mueve el pin o busca tu dirección en el mapa");
  
  // Estado para la barra de búsqueda y autocompletado
  const [searchQuery, setSearchQuery] = useState("");
  const [suggestions, setSuggestions] = useState<any[]>([]);
  const [showSuggestions, setShowSuggestions] = useState(false);

  // Cerrar sugerencias al hacer clic fuera
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (searchRef.current && !searchRef.current.contains(e.target as Node)) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Autocompletado en vivo de Nominatim cuando escribe (debounce)
  useEffect(() => {
    if (!searchQuery || searchQuery.trim().length < 3) {
      setSuggestions([]);
      setShowSuggestions(false);
      return;
    }
    const timer = setTimeout(async () => {
      try {
        const data = await searchNominatimRobust(searchQuery, selectedDistrict || selectedProvince);
        setSuggestions(data || []);
        setShowSuggestions(true);
      } catch (err) {
        console.error("Error en autocompletado:", err);
      }
    }, 350);
    return () => clearTimeout(timer);
  }, [searchQuery, selectedDistrict, selectedProvince]);

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

  const handleSearch = async (e?: React.FormEvent, queryText?: string) => {
    if (e) e.preventDefault();
    const q = queryText || searchQuery;
    if (!q.trim()) return;

    setLoadingGeo(true);
    setShowSuggestions(false);
    try {
      const data = await searchNominatimRobust(q, selectedDistrict || selectedProvince);
      if (data && data.length > 0) {
        const first = data[0];
        const lat = parseFloat(first.lat);
        const lon = parseFloat(first.lon);
        
        if (mapInstanceRef.current && markerRef.current) {
          const newLatLng = new L.LatLng(lat, lon);
          markerRef.current.setLatLng(newLatLng);
          mapInstanceRef.current.setView(newLatLng, 16);
          reverseGeocode(lat, lon, q);
        }
      } else {
        // Si Nominatim no encuentra coordenadas en el mapa satelital, igual guardamos el texto que escribió el usuario
        toast.warning("Dirección registrada en tu envío", {
          description: `No ubicamos "${q}" en el mapa satelital, pero ya lo guardamos como tu calle. Mueve el pin si quieres ajustar el punto de entrega.`,
        });
        setSelectedText(`${q}, ${selectedDistrict || selectedProvince || "Perú"}`);
        onSelectLocation({
          address: q,
          district: selectedDistrict || "Chepen",
          province: selectedProvince || "Chepen",
          department: selectedDepartment || "La Libertad",
          lat: markerRef.current?.getLatLng().lat || initialLat,
          lng: markerRef.current?.getLatLng().lng || initialLng,
        });
      }
    } catch (err) {
      console.error("Error buscando dirección:", err);
    } finally {
      setLoadingGeo(false);
    }
  };

  const handleSelectSuggestion = (sug: any) => {
    const lat = parseFloat(sug.lat);
    const lon = parseFloat(sug.lon);
    const shortName = sug.display_name?.split(",")[0] || searchQuery;
    setSearchQuery(shortName);
    setShowSuggestions(false);
    setSuggestions([]);

    if (mapInstanceRef.current && markerRef.current) {
      const newLatLng = new L.LatLng(lat, lon);
      markerRef.current.setLatLng(newLatLng);
      mapInstanceRef.current.setView(newLatLng, 16);
      reverseGeocode(lat, lon, shortName);
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
    <div className="space-y-4 my-2 bg-white dark:bg-zinc-900 p-4 sm:p-5 rounded-3xl border border-gray-200/80 dark:border-zinc-800 shadow-sm transition-all text-left">
      {/* Encabezado e Instrucciones */}
      <div className="flex items-center justify-between gap-2 border-b border-gray-100 dark:border-zinc-800 pb-3">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-xl bg-accent/10 flex items-center justify-center text-accent shrink-0">
            <MapPin className="w-4 h-4" />
          </div>
          <div>
            <h4 className="font-extrabold text-sm text-foreground">Buscador interactivo de ubicación</h4>
            <p className="text-[11px] text-muted-foreground">Busca tu dirección o mueve el pin para calcular costos exactos</p>
          </div>
        </div>
        <button
          type="button"
          onClick={handleUseGPS}
          className="text-xs bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 px-3 py-2 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs transition-all shrink-0"
          title="Detectar mi ubicación satelital"
        >
          <Navigation className="w-3.5 h-3.5" />
          <span className="hidden sm:inline">Usar mi GPS</span>
          <span className="sm:hidden">GPS</span>
        </button>
      </div>

      {/* Barra de Búsqueda Estilo Mercado Libre */}
      <div ref={searchRef} className="relative w-full">
        <form onSubmit={(e) => handleSearch(e)} className="flex gap-2">
          <div className="relative flex-1">
            <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="🔍 Escribe tu calle, avenida, urbanización o lugar (Ej: Av. Larco 123, Trujillo)..."
              className="w-full pl-10 pr-9 py-2.5 bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-2xl text-xs sm:text-sm font-semibold text-foreground placeholder:text-muted-foreground/70 focus:outline-none focus:ring-2 focus:ring-accent focus:bg-white dark:focus:bg-zinc-900 transition-all shadow-inner"
            />
            {searchQuery && (
              <button
                type="button"
                onClick={() => { setSearchQuery(""); setSuggestions([]); setShowSuggestions(false); }}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
              >
                <X className="w-4 h-4" />
              </button>
            )}
          </div>
          <button
            type="submit"
            className="px-5 py-2.5 bg-accent hover:bg-accent/90 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-sm transition-all shrink-0 flex items-center gap-1.5"
          >
            Buscar
          </button>
        </form>

        {/* Dropdown de Sugerencias de Autocompletado */}
        {showSuggestions && suggestions.length > 0 && (
          <div className="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl shadow-xl overflow-hidden divide-y divide-gray-100 dark:divide-zinc-700 max-h-60 overflow-y-auto">
            <div className="px-3 py-1.5 bg-muted/50 text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
              Sugerencias en Perú
            </div>
            {suggestions.map((sug, idx) => (
              <div
                key={idx}
                onClick={() => handleSelectSuggestion(sug)}
                className="p-3 hover:bg-accent/5 dark:hover:bg-accent/10 cursor-pointer transition-colors flex items-start gap-2.5 text-left"
              >
                <MapPin className="w-4 h-4 text-accent shrink-0 mt-0.5" />
                <div className="flex-1 truncate">
                  <p className="text-xs font-bold text-foreground truncate">
                    {sug.display_name?.split(",")[0]}
                  </p>
                  <p className="text-[11px] text-muted-foreground truncate">
                    {sug.display_name}
                  </p>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Botones rápidos de ciudades/zonas populares */}
      <div className="flex items-center gap-1.5 overflow-x-auto pb-1 text-[11px]">
        <span className="text-muted-foreground font-semibold shrink-0">Ciudades rápidas:</span>
        <button
          type="button"
          onClick={() => handleQuickCity(-7.2244, -79.4328, "Chepen")}
          className="px-2.5 py-1 bg-gray-100/80 dark:bg-zinc-800 hover:bg-accent hover:text-white border border-gray-200/60 dark:border-zinc-700 rounded-xl font-bold transition-all shrink-0 cursor-pointer"
        >
          📍 Chepén
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-8.1159, -79.0299, "Trujillo")}
          className="px-2.5 py-1 bg-gray-100/80 dark:bg-zinc-800 hover:bg-accent hover:text-white border border-gray-200/60 dark:border-zinc-700 rounded-xl font-bold transition-all shrink-0 cursor-pointer"
        >
          📍 Trujillo
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-12.0464, -77.0428, "Lima")}
          className="px-2.5 py-1 bg-gray-100/80 dark:bg-zinc-800 hover:bg-accent hover:text-white border border-gray-200/60 dark:border-zinc-700 rounded-xl font-bold transition-all shrink-0 cursor-pointer"
        >
          📍 Lima
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-6.7714, -79.8409, "Chiclayo")}
          className="px-2.5 py-1 bg-gray-100/80 dark:bg-zinc-800 hover:bg-accent hover:text-white border border-gray-200/60 dark:border-zinc-700 rounded-xl font-bold transition-all shrink-0 cursor-pointer"
        >
          📍 Chiclayo
        </button>
        <button
          type="button"
          onClick={() => handleQuickCity(-16.409, -71.5375, "Arequipa")}
          className="px-2.5 py-1 bg-gray-100/80 dark:bg-zinc-800 hover:bg-accent hover:text-white border border-gray-200/60 dark:border-zinc-700 rounded-xl font-bold transition-all shrink-0 cursor-pointer"
        >
          📍 Arequipa
        </button>
      </div>

      {/* Contenedor del Mapa */}
      <div
        ref={mapContainerRef}
        className="w-full h-56 sm:h-64 rounded-2xl overflow-hidden border-2 border-accent/20 shadow-inner z-10 relative bg-zinc-100 dark:bg-zinc-800"
      />

      {/* Barra de estado / Dirección detectada (Estilo confirmación de zona) */}
      <div className="flex items-center justify-between gap-3 bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 p-3 rounded-2xl text-xs shadow-2xs">
        <div className="flex items-center gap-2 truncate">
          {loadingGeo ? (
            <Loader2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400 animate-spin shrink-0" />
          ) : (
            <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
          )}
          <span className="font-extrabold text-emerald-700 dark:text-emerald-300 shrink-0">Ubicación elegida:</span>
          <span className="font-bold text-foreground truncate">
            {loadingGeo ? "Sincronizando dirección y distrito..." : selectedText}
          </span>
        </div>
        <span className="text-[10px] bg-white/80 dark:bg-zinc-800 text-emerald-700 dark:text-emerald-300 font-extrabold px-2 py-0.5 rounded-lg border border-emerald-500/20 shrink-0 hidden sm:inline">
          ✓ Envíos habilitados
        </span>
      </div>
    </div>
  );
}
