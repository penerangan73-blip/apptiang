import type { LucideIcon } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { cn } from "@/lib/utils";

type StatCardProps = {
  label: string;
  value: string | number;
  description: string;
  icon: LucideIcon;
  tone: "cyan" | "emerald" | "rose" | "amber";
};

const toneClasses: Record<StatCardProps["tone"], string> = {
  cyan: "bg-primary/10 text-primary ring-primary/30",
  emerald: "bg-emerald-50 text-emerald-700 ring-emerald-200",
  rose: "bg-rose-50 text-rose-700 ring-rose-200",
  amber: "bg-amber-50 text-amber-700 ring-amber-200",
};

export function StatCard({ label, value, description, icon: Icon, tone }: StatCardProps) {
  return (
    <Card className="border-border bg-card shadow-sm">
      <CardContent className="p-4 sm:p-5">
        <div className="flex items-start justify-between gap-4">
          <div className="min-w-0">
            <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">{label}</p>
            <p className="mt-2 text-2xl font-bold text-foreground">{value}</p>
            <p className="mt-1 text-sm text-muted-foreground">{description}</p>
          </div>
          <div className={cn("flex size-11 shrink-0 items-center justify-center rounded-lg ring-1", toneClasses[tone])}>
            <Icon className="size-5" />
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
