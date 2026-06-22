import Link from "next/link";

export function CategoryGridBlock({ data }: { data: any }) {
  if (!data.categories || data.categories.length === 0) return null;

  return (
    <section>
      <div className="flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
        <h2 className="text-2xl font-bold text-gray-900">{data.title || 'Compra por Categoría'}</h2>
      </div>
      <div className="flex flex-wrap gap-3">
        {data.categories.map((cat: any) => (
          <Link 
            key={cat.id} 
            href={`/productos?category=${cat.id}`}
            className="group px-6 py-3 bg-white border border-gray-200 rounded-full font-semibold text-sm text-gray-700 hover:border-accent hover:text-accent hover:shadow-md transition-all flex items-center gap-2 active:scale-95 duration-200 ease-[var(--spring-easing)]"
          >
            <span className="w-2 h-2 rounded-full bg-gray-300 group-hover:bg-accent transition-colors"></span>
            {cat.name}
          </Link>
        ))}
      </div>
    </section>
  );
}
