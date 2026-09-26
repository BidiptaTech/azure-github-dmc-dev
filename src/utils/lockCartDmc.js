import { store } from "@/store/store";
import { setSelectedDmcId } from "@/slice/dmc/dmcSlice";

export const resolveItemDmcId = (item = {}) => {
  const raw =
    item?.dmc_id ??
    item?.dmc_Id ??
    item?.dmcId ??
    item?.prices?.dmc_id ??
    null;
  if (raw == null || raw === "") return null;
  const parsed = Number(raw);
  return Number.isFinite(parsed) ? parsed : null;
};

export const buildDmcDataFromState = (dmcId, state) => {
  const existing = state.dmc?.selectedDmcData;
  if (existing && Number(existing.dmcId) === Number(dmcId)) {
    return existing;
  }

  const list = Array.isArray(state.dmc?.dmcs?.data)
    ? state.dmc.dmcs.data
    : Array.isArray(state.dmc?.dmcs)
      ? state.dmc.dmcs
      : [];
  const found = list.find((dmc) => Number(dmc.userId) === Number(dmcId));

  if (found) {
    return {
      id: `dmc-${found.userId}`,
      dmcId: found.userId,
      name: found.company_name || found.name || `DMC ${found.userId}`,
      location: found.country || "",
      logo: found.logo || "",
      description: found.description || "Cart DMC",
      originalData: {
        ...found,
        price_hide: found.price_hide || 0,
        zone_on: found.zone_on || 0,
      },
    };
  }

  return {
    id: `dmc-${dmcId}`,
    dmcId: Number(dmcId),
    name: state.dmc?.selectedDmcCompanyName || `DMC ${dmcId}`,
    location: existing?.location || "",
    logo: state.dmc?.selectedDmcLogo || "",
    description: "Cart locked DMC",
    originalData: {
      userId: Number(dmcId),
      company_name: state.dmc?.selectedDmcCompanyName || `DMC ${dmcId}`,
      logo: state.dmc?.selectedDmcLogo || "",
      price_hide: existing?.originalData?.price_hide ?? 0,
      zone_on: existing?.originalData?.zone_on ?? 0,
    },
  };
};

/**
 * Lock the active DMC in Redux (same path as DmcFilter / setSelectedDmcId)
 * when a product is added to cart. Returns the numeric dmc id used.
 */
export const lockCartDmc = (dispatch, itemOrId) => {
  if (!dispatch) return null;

  const state = store.getState();
  const fromArg =
    typeof itemOrId === "object" && itemOrId !== null
      ? resolveItemDmcId(itemOrId)
      : itemOrId != null && itemOrId !== ""
        ? Number(itemOrId)
        : null;

  const dmcId =
    (Number.isFinite(fromArg) ? fromArg : null) ||
    state.dmc?.dmcId ||
    null;

  if (!dmcId) return null;

  const dmcData = buildDmcDataFromState(dmcId, state);
  dispatch(
    setSelectedDmcId({
      dmcId: Number(dmcId),
      dmcData,
    })
  );

  return Number(dmcId);
};
