import type { PoleCondition } from "@/lib/types";
import { Badge } from "@/components/ui/badge";

const conditionLabels: Record<PoleCondition, string> = {
  baik: "Baik",
  rusak: "Rusak",
  perbaikan: "Perbaikan",
};

const conditionVariants: Record<PoleCondition, "success" | "destructive" | "warning"> = {
  baik: "success",
  rusak: "destructive",
  perbaikan: "warning",
};

export function StatusBadge({ condition, className }: { condition: PoleCondition; className?: string }) {
  return <Badge variant={conditionVariants[condition]} className={className}>{conditionLabels[condition]}</Badge>;
}
