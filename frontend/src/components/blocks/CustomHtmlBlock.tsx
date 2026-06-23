export function CustomHtmlBlock({ data }: { data: any }) {
  if (!data.content) return null;

  return (
    <section className="w-full">
      {/* We use dangerouslySetInnerHTML to render the raw HTML from the database */}
      <div 
        className="w-full custom-html-container"
        dangerouslySetInnerHTML={{ __html: data.content }}
      />
    </section>
  );
}
