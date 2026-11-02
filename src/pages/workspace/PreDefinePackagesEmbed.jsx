import PreDefinePackages from "@/components/pre-define-packages";

/** Pre-define packages without Header11 (shell comes from PartnerLayout). */
export default function PreDefinePackagesEmbed() {
  return (
    <div style={{ minHeight: 480 }}>
      <PreDefinePackages />
    </div>
  );
}
