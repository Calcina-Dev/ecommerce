import Link from "next/link";

export function ValuePropBlock({ data }: { data: any }) {
  return (
    <section className="bg-gray-900 text-white py-20 mt-12">
      <div className="max-w-7xl mx-auto px-6 text-center space-y-6">
        <h2 className="text-3xl md:text-5xl font-bold">{data.title || 'Propuesta de Valor'}</h2>
        {data.description && (
          <p className="text-gray-400 max-w-2xl mx-auto text-lg whitespace-pre-wrap">{data.description}</p>
        )}
        {data.button_text && data.button_link && (
          <div className="pt-8">
            <Link href={data.button_link} className="inline-block border-2 border-white text-white hover:bg-white hover:text-gray-900 px-8 py-3 rounded-full font-bold transition-colors">
              {data.button_text}
            </Link>
          </div>
        )}
      </div>
    </section>
  );
}
