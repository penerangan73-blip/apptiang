import { Bolt, MapPin } from "lucide-react";
import { Button } from "@/components/ui/button";
import { StatusBadge } from "@/components/status-badge";
import type { Pole } from "@/lib/types";

type PoleListItemProps = {
  pole: Pole;
  onEdit: (pole: Pole) => void;
  onReport: (pole: Pole) => void;
};

export function PoleListItem({ pole, onEdit, onReport }: PoleListItemProps) {
  return (
    <article className="rounded-lg border bg-background/65 p-4 shadow-sm">
      <div className="flex items-start justify-between gap-3">
        <div>
          <h3 className="font-bold text-foreground">{pole.customer_id}</h3>
          <p className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
            <MapPin className="size-4 text-primary" />
            {pole.street_name}
          </p>
        </div>
        <StatusBadge condition={pole.condition} />
      </div>

      <div className="mt-4 flex items-center justify-between gap-3">
        <p className="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground">
          <Bolt className="size-4 text-amber-500" />
          {pole.power_usage} Watt
        </p>
        <div className="flex gap-2">
          <Button type="button" variant="outline" size="sm" onClick={() => onEdit(pole)}>
            Edit
          </Button>
          <Button type="button" size="sm" onClick={() => onReport(pole)}>
            Lapor
          </Button>
        </div>
      </div>
    </article>
  );
}
